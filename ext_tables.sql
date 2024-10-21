CREATE TABLE tx_nst3ai_domain_model_localizationlog
(
    status tinyint(4) DEFAULT '0' NOT NULL,
    translation_mode tinyint(4) DEFAULT '0' NOT NULL,
    extension_name  varchar(255) default '',
    source_file  varchar(255) default '',
    output_file  varchar(255) default '',
    content text,
);