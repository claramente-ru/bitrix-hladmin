CREATE TABLE IF NOT EXISTS `claramente_hladmin_sections`
(
    `ID`   int                                                           NOT NULL AUTO_INCREMENT,
    `NAME` varchar(255) CHARACTER SET utf8mb4  NOT NULL,
    `SORT` int                                                           NOT NULL DEFAULT '100',
    `CODE` varchar(32)                                                   NOT NULL,
    PRIMARY KEY (`ID`),
    UNIQUE KEY `CODE` (`CODE`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `claramente_hladmin_hlblocks`
(
    `ID`         int NOT NULL AUTO_INCREMENT,
    `HLBLOCK_ID` int unsigned NOT NULL,
    `SECTION_ID` int DEFAULT NULL,
    `SORT`       int NOT NULL,
    PRIMARY KEY (`ID`),
    UNIQUE KEY `HLBLOCK_ID` (`HLBLOCK_ID`) USING BTREE,
    KEY          `SECTION_ID` (`SECTION_ID`) USING BTREE,
    CONSTRAINT `claramente_hladmin_hlblocks_ibfk_3` FOREIGN KEY (`HLBLOCK_ID`) REFERENCES `b_hlblock_entity` (`ID`) ON DELETE CASCADE,
    CONSTRAINT `claramente_hladmin_hlblocks_ibfk_4` FOREIGN KEY (`SECTION_ID`) REFERENCES `claramente_hladmin_sections` (`ID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;