<?php

declare(strict_types=1);

namespace NITSAN\NsT3AiLocalization\Utility;

use DOMXPath;
use Exception;
use DOMDocument;
use SimpleXMLElement;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use NITSAN\NsT3AiLocalization\Exception\EmptyXliffException;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class XliffUtility
{
    public const LANGUAGE_DIR = 'Resources/Private/Language';


    /**
     * @throws FileNotFoundException
     * @throws Exception
     * @throws EmptyXliffException
     */
    public static function getTranslateValues(array $data): array
    {
        $targetData = [];
        if ($data['translationMode'] === '1') {
            $targetData = self::readXliff($data['extensionKey'], $data['targetLanguage'] . '.' . $data['filename'], false);
            $targetData = array_keys($targetData);
        }
        $neededTranslations = self::readXliff($data['extensionKey'], $data['filename']);

        // cleanup translation items
        foreach ($neededTranslations as $transKey => $transValue) {
            if ($data['translationMode'] === '1' && in_array($transKey, $targetData)) {
                unset($neededTranslations[$transKey]);
            } else {
                unset($neededTranslations[$transKey]['originalData']);
            }
        }
        return $neededTranslations;
    }

    /**
     * @throws FileNotFoundException
     * @throws \Exception
     */
    public static function readXliff(string $extKey, string $filename, bool $sourceFile = true): array
    {
        $packageManager = GeneralUtility::makeInstance(PackageManager::class);
        $package = $packageManager->getPackage($extKey);
        $packagePath = $package->getPackagePath();
        $file = $packagePath . static::LANGUAGE_DIR.'/' . $filename;

        try {
            $fileData = file_get_contents($file);
            if ($fileData === false) {
                throw new FileNotFoundException('File ' . $file . ' not found in EXT:' . $extKey . '.');
            }
        } catch (\Exception $e) {
            throw new FileNotFoundException('File ' . $file . ' not found in EXT:' . $extKey . '.');
        }

        $xmlData = new \SimpleXMLElement($fileData);
        $rawData = self::simpleXMLElementToArray($xmlData);
        if (empty($rawData['file']['body']) && $sourceFile) {
            throw new EmptyXliffException(LocalizationUtility::translate('translation.sourceXliffFileEmpty.title', 'ns_t3ai_localization'));
        } elseif (empty($rawData['file']['body']) && !$sourceFile) {
            $rawData['file']['body'] = [];
        }
        return  self::xmlArrayToStructuredArray($rawData) ?: [];
    }


    public static function simpleXMLElementToArray(SimpleXMLElement $xml)
    {
        $attributes = $xml->attributes();
        $children = $xml->children();
        $text = trim((string) $xml);

        if (count($children) === 0 && count($attributes) === 0) {
            return $text;
        }

        $arr = [];
        foreach ($attributes as $k => $v) {
            $arr['@' . $k] = (string) $v;
        }

        foreach ($children as $child) {
            $childName = $child->getName();

            if (count($xml->$childName) > 1) {
                $arr[$childName][] = self::simpleXMLElementToArray($child);
            } else {
                $arr[$childName] = self::simpleXMLElementToArray($child);
            }
        }

        if (!empty($text)) {
            $arr['_text'] = $text;
        }

        return $arr;
    }


    protected static function xmlArrayToStructuredArray(array $rawData): array
    {
        $data = [];
        if (array_key_exists('file', $rawData) && array_key_exists('body', $rawData['file']) && array_key_exists('trans-unit', $rawData['file']['body'])) {
            if (array_key_exists('0', $rawData['file']['body']['trans-unit'])) {
                foreach ($rawData['file']['body']['trans-unit'] as $langItem) {
                    if (is_array($langItem) && array_key_exists('@id', $langItem)) {
                        $data[$langItem['@id']] = [
                            'originalData' => $langItem,
                            'source' => array_key_exists('source', $langItem) ? $langItem['source'] : '',
                            'target' => array_key_exists('target', $langItem) ? $langItem['target'] : '',
                        ];
                    }
                }
            } elseif (is_array($rawData['file']['body']['trans-unit'])) {
                $itemData = [];
                foreach ($rawData['file']['body']['trans-unit'] as $key => $itemValue) {
                    $itemData[$key] = $itemValue;
                }
                $data[$itemData['@id']] = [
                    'originalData' => $itemData,
                    'source' => array_key_exists('source', $itemData) ? $itemData['source'] : '',
                    'target' => array_key_exists('target', $itemData) ? $itemData['target'] : '',
                ];
            }
        }
        return $data;
    }

    /**
     * @throws FileNotFoundException
     */
    public static function writeXliff(array $input): string
    {
        $packageManager = GeneralUtility::makeInstance(PackageManager::class);
        $package = $packageManager->getPackage($input['extensionKey']);
        $packagePath = $package->getPackagePath();
        $translations = $input['translations'];
        $status = false;
        $content = '';
        $file = $packagePath . static::LANGUAGE_DIR.'/' .$input['targetLanguage'] . '.' . $input['filename'];
        $existingXml = file_exists($file) ? file_get_contents($file) : '';

        $sourceFile = $packagePath . static::LANGUAGE_DIR.'/' . $input['filename'];
        $sourceXml = file_exists($sourceFile) ? file_get_contents($sourceFile) : '';

        if ($input['translationMode'] === '1') {
            $updatedXml = self::appendMissingTranslations($existingXml, $input['translations']);
            $content = self::computeDiff($sourceXml, $existingXml, $updatedXml, 1);

        } else {
            $updatedXml = self::arrayToXlfXml($translations, $input['targetLanguage']);

            if($existingXml) {
                $content = self::computeDiff($sourceXml, $existingXml, $updatedXml, 0);
            } else {
                $content = self::computeDiff($sourceXml, '', $updatedXml, 0);
            }
        }

        if (GeneralUtility::writeFile($file, $updatedXml) !== false) {
            $status = true;
        }
        return json_encode(['content' => $content, 'status' => $status]);
    }


    public static function arrayToXlfXml(array $data, string $targetLanguage): string
    {

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $xliff = $dom->createElement('xliff');
        $xliff->setAttribute('version', '1.2');
        $dom->appendChild($xliff);

        $file = $dom->createElement('file');
        $file->setAttribute('source-language', 'en');
        $file->setAttribute('target-language', $targetLanguage);
        $file->setAttribute('datatype', 'plaintext');
        $file->setAttribute('original', 'messages');
        $xliff->appendChild($file);

        $body = $dom->createElement('body');
        $file->appendChild($body);

        foreach ($data as $key => $value) {
            $transUnit = $dom->createElement('trans-unit');
            $transUnit->setAttribute('id', $key);
            $transUnit->setAttribute('resname', $key);

            $target = $dom->createElement('target', htmlspecialchars($value));
            $transUnit->appendChild($target);

            $body->appendChild($transUnit);
        }

        return $dom->saveXML();
    }


    public static function getFileList(array $extension): array
    {
        $files = [];
        $languageDirectory = $extension['packagePath'] . static::LANGUAGE_DIR;
        $allLanguageFiles = GeneralUtility::getAllFilesAndFoldersInPath([], $languageDirectory . '/', 'xlf', false, 3);
        foreach ($allLanguageFiles as $file) {
            $filename = str_replace($languageDirectory . '/', '', $file);
            $parts = explode('.', $filename);
            if (count($parts) !== 2 || $parts[0] === '') {
                continue;
            }
            $files[$filename] = $filename;
        }
        return $files;
    }

    public static function appendMissingTranslations($existingXml, $newData): string
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($existingXml);
        $body = $dom->getElementsByTagName('body')->item(0);

        foreach ($newData as $key => $value) {
            if (!$dom->getElementById($key)) {
                $transUnit = $dom->createElement('trans-unit');
                $transUnit->setAttribute('id', $key);
                $transUnit->setAttribute('resname', $key);

                $target = $dom->createElement('target', htmlspecialchars($value));
                $transUnit->appendChild($target);

                $body->appendChild($transUnit);
            }
        }
        return $dom->saveXML();
    }


    public static function computeDiff($source_text, $old_text = '', $new_text = '', int $mode = 0): string
    {
        // Create a DOMDocument for source_text XML
        $sourceTextDoc = new DOMDocument();
        $sourceTextDoc->loadXML($source_text);

        if($old_text) {
            $source_doc = new DOMDocument();
            $source_doc->loadXML($old_text);
            $source_xpath = new DOMXPath($source_doc);
        }

        // Create a new DOMDocument for new_text XML
        $target_doc = new DOMDocument();
        $target_doc->loadXML($new_text);

        // Create XPath instances for source and target XML
        $target_xpath = new DOMXPath($target_doc);
        $sourceDoc_xpath = new DOMXPath($sourceTextDoc);

        $target_trans_units = $target_xpath->query('//trans-unit');
        $diff = [];

        foreach ($target_trans_units as $target_trans_unit) {

            $target_id = $target_trans_unit->getAttribute('id');
            $target_text = $target_xpath->evaluate('string(target)', $target_trans_unit);
            $parent_source = $sourceDoc_xpath->query("//trans-unit[@id='$target_id']")->item(0);
            $parent_text = $sourceDoc_xpath->evaluate('string(source)', $parent_source);

            if(!$old_text) {
                $diff[$target_id] = [$parent_text,'<ins>' . htmlspecialchars(strip_tags($target_text)) . "</ins>"];
            } else {
                $source_trans_unit = $source_xpath->query("//trans-unit[@id='$target_id']")->item(0);
                if($source_trans_unit && $mode === 0) {
                    $source_target_text = $source_xpath->evaluate('string(target)', $source_trans_unit);
                    $oldText = '<del>' . htmlspecialchars(strip_tags($source_target_text)) . "</del>";
                    $newText = '<ins>' . htmlspecialchars(strip_tags($target_text)) . "</ins><br>";
                    $diff[$target_id] = [$parent_text, $oldText.$newText];
                } elseif (!$source_trans_unit) {
                    $diff[$target_id] = [$parent_text,'<ins>' . htmlspecialchars(strip_tags($target_text)) . "</ins>"];
                }

            }
        }
        return json_encode($diff);
    }

}
