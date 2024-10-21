<?php

declare(strict_types=1);

namespace NITSAN\NsT3AiLocalization\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class LocalizationLog extends AbstractEntity
{
    /**
    * @var bool
    */
    protected bool $status = false;

    /**
    * @var int
    */
    protected int $translationMode = 0;

    /**
    * @var string
    */
    protected string $extensionName = '';

    /**
    * @var string
    */
    protected string $sourceFile = '';

    /**
    * @var string
    */
    protected string $outputFile = '';

    /**
    * @var string
    */
    protected string $content = '';

    /**
     * @return bool
     */
    public function getStatus(): bool
    {
        return $this->status;
    }

    /**
     * @param bool $status
     */
    public function setStatus(bool $status): void
    {
        $this->status = $status;
    }

    /**
     * @return int
     */
    public function getTranslationMode(): int
    {
        return $this->translationMode;
    }

    /**
     * @param int $translationMode
     */
    public function setTranslationMode(int $translationMode): void
    {
        $this->translationMode = $translationMode;
    }

    /**
     * @return string
     */
    public function getExtensionName(): string
    {
        return $this->extensionName;
    }

    /**
     * @param string $extensionName
     */
    public function setExtensionName(string $extensionName): void
    {
        $this->extensionName = $extensionName;
    }

    /**
     * @return string
     */
    public function getSourceFile(): string
    {
        return $this->sourceFile;
    }

    /**
     * @param string $sourceFile
     */
    public function setSourceFile(string $sourceFile): void
    {
        $this->sourceFile = $sourceFile;
    }

    /**
     * @return string
     */
    public function getOutputFile(): string
    {
        return $this->outputFile;
    }

    /**
     * @param string $outputFile
     */
    public function setOutputFile(string $outputFile): void
    {
        $this->outputFile = $outputFile;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getContentArray(): array
    {
        return $this->content ? json_decode($this->content, true) : [];
    }

    public function getKeys(): string
    {
        if($this->content) {
            return  implode(',', array_keys($this->getContentArray()));
        }
        return '';
    }
}