CREATE TABLE IF NOT EXISTS claramente_hladmin_sections
(
    id   SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    sort INT NOT NULL DEFAULT 100,
    code VARCHAR(32) NOT NULL UNIQUE
    );

CREATE TABLE IF NOT EXISTS claramente_hladmin_hlblocks
(
    id         SERIAL PRIMARY KEY,
    hlblock_id INT NOT NULL UNIQUE,
    section_id INT DEFAULT NULL,
    sort       INT NOT NULL,
    CONSTRAINT fk_hlblock FOREIGN KEY (hlblock_id) REFERENCES b_hlblock_entity (id) ON DELETE CASCADE,
    CONSTRAINT fk_section FOREIGN KEY (section_id) REFERENCES claramente_hladmin_sections (id) ON DELETE SET NULL
    );

CREATE INDEX idx_section_id ON claramente_hladmin_hlblocks (section_id);