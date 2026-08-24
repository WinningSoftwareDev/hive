DROP SCHEMA IF EXISTS Authentication;
DROP SCHEMA IF EXISTS Core;

CREATE SCHEMA Authentication;
CREATE SCHEMA Core;

CREATE TABLE Authentication.tblUser (
    intUserId INT UNSIGNED NOT NULL AUTO_INCREMENT,
    strEmail VARCHAR(180) NOT NULL,
    strPassword VARCHAR(255) DEFAULT NULL COMMENT 'Hashed password (null for OAuth-only users)',
    bolVerified TINYINT(1) NOT NULL DEFAULT 0,
    bolActive TINYINT(1) NOT NULL DEFAULT 1,
    dtmCreated DATETIME NOT NULL DEFAULT NOW(),
    dtmUpdated DATETIME ON UPDATE NOW(),
    PRIMARY KEY (intUserId),
    UNIQUE KEY UK_tblUser_strEmail (strEmail),
    INDEX I_tblUser_bolActive (bolActive),
    INDEX I_tblUser_bolVerified (bolVerified),
    INDEX I_tblUser_dtmCreated (dtmCreated)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE Authentication.tblRole (
    intRoleId INT UNSIGNED NOT NULL AUTO_INCREMENT,
    strRoleName VARCHAR(50) NOT NULL,
    strHandle VARCHAR(50) NOT NULL,
    dtmCreated DATETIME NOT NULL DEFAULT NOW(),
    dtmUpdated DATETIME ON UPDATE NOW(),
    PRIMARY KEY (intRoleId),
    UNIQUE KEY UK_tblRole_strHandle (strHandle)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE Authentication.tblPermission (
    intPermissionId INT UNSIGNED NOT NULL AUTO_INCREMENT,
    strPermissionName VARCHAR(50) NOT NULL,
    strHandle VARCHAR(50) NOT NULL,
    dtmCreated DATETIME NOT NULL DEFAULT NOW(),
    dtmUpdated DATETIME ON UPDATE NOW(),
    PRIMARY KEY (intPermissionId),
    UNIQUE KEY UK_tblPermission_strHandle (strHandle)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE Authentication.tblRolePermission (
    intRoleId INT UNSIGNED NOT NULL,
    intPermissionId INT UNSIGNED NOT NULL,
    dtmCreated DATETIME NOT NULL DEFAULT NOW(),
    dtmUpdated DATETIME ON UPDATE NOW(),
    PRIMARY KEY (intRoleId, intPermissionId),
    CONSTRAINT FK_tblRolePermission_intRoleId 
        FOREIGN KEY (intRoleId)
            REFERENCES Authentication.tblRole(intRoleId),
    CONSTRAINT FK_tblRolePermission_intPermissionId 
        FOREIGN KEY (intPermissionId)
            REFERENCES Authentication.tblPermission(intPermissionId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE Authentication.tblUserRole (
    intUserId INT UNSIGNED NOT NULL,
    intRoleId INT UNSIGNED NOT NULL,
    dtmCreated DATETIME NOT NULL DEFAULT NOW(),
    dtmUpdated DATETIME ON UPDATE NOW(),
    PRIMARY KEY (intUserId, intRoleId),
    CONSTRAINT FK_tblUserRole_intUserId 
        FOREIGN KEY (intUserId) 
            REFERENCES Authentication.tblUser(intUserId),
    CONSTRAINT FK_tblUserRole_intRoleId 
        FOREIGN KEY (intRoleId) 
            REFERENCES Authentication.tblRole(intRoleId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE Authentication.tblEmailVerificationToken (
    intEmailVerificationTokenId INT UNSIGNED NOT NULL AUTO_INCREMENT,
    intUserId INT UNSIGNED NOT NULL,
    strToken VARCHAR(100) NOT NULL,
    dtmExpires DATETIME NOT NULL,
    dtmVerified DATETIME,
    dtmCreated DATETIME NOT NULL DEFAULT NOW(),
    dtmUpdated DATETIME ON UPDATE NOW(),
    PRIMARY KEY (intEmailVerificationTokenId),
    CONSTRAINT FK_tblVerificationToken_intUserId 
        FOREIGN KEY FK_tblVerificationToken_intUserId (intUserId)
            REFERENCES Authentication.tblUser(intUserId),
    UNIQUE KEY UK_tblVerificationToken_strToken (strToken),
    INDEX I_tblVerificationToken_intUserId (intUserId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE Authentication.tblPasswordResetToken (
    intPasswordResetTokenId INT UNSIGNED NOT NULL AUTO_INCREMENT,
    intUserId INT UNSIGNED NOT NULL,
    strToken VARCHAR(100) NOT NULL,
    dtmExpires DATETIME NOT NULL,
    dtmCreated DATETIME NOT NULL DEFAULT NOW(),
    dtmUpdated DATETIME ON UPDATE NOW(),
    PRIMARY KEY (intPasswordResetTokenId),
    CONSTRAINT FK_tblPasswordResetToken_intUserId
        FOREIGN KEY (intUserId)
            REFERENCES Authentication.tblUser(intUserId),
    UNIQUE KEY UK_tblPasswordResetToken_strToken (strToken),
    INDEX I_tblPasswordResetToken_intUserId (intUserId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE Core.ublEmailType (
    intEmailTypeId INT UNSIGNED NOT NULL AUTO_INCREMENT,
    strEmailTypeSubject VARCHAR(255) NOT NULL,
    strEmailTypeHandle VARCHAR(100) NOT NULL,
    strTemplate VARCHAR(255) NOT NULL,
    PRIMARY KEY (intEmailTypeId),
    UNIQUE KEY UK_ublEmailType_strEmailType (strEmailTypeHandle)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO Core.ublEmailType
    (strEmailTypeSubject, strEmailTypeHandle, strTemplate)
VALUES
    ('Verify your email address', 'VERIFY_EMAIL_ADDRESS', 'Core/Email/verify-email.latte'),
    ('Reset your password', 'PASSWORD_RESET', 'Core/Email/reset-password.latte');

CREATE TABLE Core.ublOauthProvider (
    intOauthProviderId INT UNSIGNED NOT NULL AUTO_INCREMENT,
    strOauthProviderHandle VARCHAR(32) NOT NULL,
    strOauthProviderLabel VARCHAR(100) NOT NULL,
    strIcon VARCHAR(100) DEFAULT NULL,
    PRIMARY KEY (intOauthProviderId),
    UNIQUE KEY UK_ublOauthProvider_strHandle (strOauthProviderHandle)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO Core.ublOauthProvider
    (strOauthProviderHandle, strOauthProviderLabel, strIcon)
VALUES
    ('github', 'GitHub', 'fa-brands fa-github'),
    ('google', 'Google', 'fa-brands fa-google');

CREATE TABLE Authentication.tblUserOauth (
    intUserOauthId INT UNSIGNED NOT NULL AUTO_INCREMENT,
    intUserId INT UNSIGNED NOT NULL,
    intOauthProviderId INT UNSIGNED NOT NULL,
    strOauthProviderId VARCHAR(255) NOT NULL COMMENT 'Provider user ID (e.g. GitHub user ID, Google sub)',
    dtmCreated DATETIME NOT NULL DEFAULT NOW(),
    dtmUpdated DATETIME ON UPDATE NOW(),
    PRIMARY KEY (intUserOauthId),
    UNIQUE KEY UK_tblUserOauth_provider (intOauthProviderId, strOauthProviderId),
    INDEX I_tblUserOauth_intUserId (intUserId),
    CONSTRAINT FK_tblUserOauth_intUserId 
        FOREIGN KEY (intUserId) 
            REFERENCES Authentication.tblUser(intUserId),
    CONSTRAINT FK_tblUserOauth_intOauthProviderId
        FOREIGN KEY (intOauthProviderId)
            REFERENCES Core.ublOauthProvider(intOauthProviderId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO Authentication.tblRole
    (strRoleName, strHandle)
VALUES
    ('User', 'ROLE_USER'),
    ('Administrator', 'ROLE_ADMIN');
