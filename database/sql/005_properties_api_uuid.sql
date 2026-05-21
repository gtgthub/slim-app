-- API sync: external property identifier for upsert (manual rows keep api_uuid NULL)
ALTER TABLE properties
    ADD COLUMN api_uuid CHAR(36) NULL DEFAULT NULL AFTER source,
    ADD UNIQUE KEY uk_properties_api_uuid (api_uuid);
