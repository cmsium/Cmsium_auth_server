CREATE TABLE files
(
    file_id VARCHAR(32) PRIMARY KEY NOT NULL,
    path VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    owner_id VARCHAR(32) NOT NULL,
    name VARCHAR(100) NOT NULL
);
CREATE UNIQUE INDEX files_file_id_uindex ON files (file_id);

CREATE TABLE file_links
(
    file_id VARCHAR(32) NOT NULL,
    link VARCHAR(32) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT file_id FOREIGN KEY (file_id) REFERENCES files (file_id)
);
CREATE INDEX file_id_idx ON file_links (file_id);
CREATE UNIQUE INDEX file_id_UNIQUE ON file_links (file_id);
CREATE UNIQUE INDEX file_links_link_uindex ON file_links (link);


CREATE TABLE roles_in_files
(
    role_id INT(11) NOT NULL,
    file_id VARCHAR(32) NOT NULL,
    permissions INT(11) DEFAULT '7' NOT NULL
);
CREATE INDEX roles_in_files_files_file_id_fk ON roles_in_files (file_id);

CREATE PROCEDURE `checkFileLink`(IN idFile VARCHAR(32))
BEGIN
     SELECT link FROM file_links WHERE file_id=idFile;
END;

CREATE  PROCEDURE `generateFileLink`(IN idFile VARCHAR(32))
BEGIN
     INSERT INTO file_links (file_id, link) VALUES (idFile,md5(concat(file_id,NOW())));
     call checkFileLink(idFile);
END;

CREATE PROCEDURE `getFileIdByLink`(IN inLink VARCHAR(32))
BEGIN
     SELECT file_id FROM file_links WHERE link=inLink;
END;

CREATE PROCEDURE `getFilePathById`(IN idFile VARCHAR(32))
BEGIN
    SELECT CONCAT_WS('_', file_id, UNIX_TIMESTAMP(created_at), MD5(name)) as result FROM files WHERE file_id = idFile ;
END;

CREATE PROCEDURE `createFileRoles`(IN idFile VARCHAR(32),IN idRole INT,IN inPermissions INT)
BEGIN
     INSERT INTO roles_in_files (role_id, file_id, permissions) VALUES (idRole, idFile,inPermissions);
END;

CREATE PROCEDURE `deleteFileRoles`(IN idFile VARCHAR(32))
BEGIN
     DELETE FROM roles_in_files WHERE file_id=idFile;
END;

CREATE PROCEDURE `getFileRoles`(IN idFile VARCHAR(32))
BEGIN
     SELECT role_id,permissions FROM roles_in_files WHERE file_id = idFile;
END;

CREATE PROCEDURE createFile(IN idFile VARCHAR(32),IN fileName VARCHAR(100),IN idOwner VARCHAR(32))
  BEGIN
    INSERT INTO files (file_id, path, created_at, name, owner_id) VALUES (idFile,'future',NOW(),fileName,idOwner);
  END;

CREATE PROCEDURE getFileOwner(IN idFile VARCHAR(32))
  BEGIN
    SELECT owner_id FROM files WHERE file_id = idFile;
  END;

CREATE PROCEDURE `deleteFile`(IN idFile VARCHAR(32))
  BEGIN
    DELETE FROM files WHERE file_id=idFile;
  END;

CREATE PROCEDURE `deleteLinkByFileId`(IN idFile VARCHAR(32))
  BEGIN
    DELETE FROM file_links WHERE file_id=idFile;
  END;

CREATE PROCEDURE `getFileNameById`(IN idFile VARCHAR(32))
  BEGIN
    SELECT name FROM files WHERE file_id = idFile ;
  END;

create table sandbox_files
(
  file_id varchar(32) not null
    primary key,
  type VARCHAR(255) not null,
  created_at timestamp default CURRENT_TIMESTAMP not null,
  owner_id varchar(32) not null,
  name varchar(100) not null,
  constraint sandbox_files_file_id_uindex
  unique (file_id)
)
;
CREATE PROCEDURE createFileInSandbox(IN idFile VARCHAR(32),IN fileType VARCHAR(255), IN fileName VARCHAR(100), IN idOwner VARCHAR(32))
  BEGIN
    INSERT INTO sandbox_files (file_id,type, created_at, name, owner_id) VALUES (idFile,fileType,NOW(),fileName,idOwner);
  END;

CREATE PROCEDURE getFileDataFromSandbox(IN idFile VARCHAR(32))
  BEGIN
    SELECT * FROM sandbox_files WHERE file_id = idFile;
  END;

CREATE PROCEDURE createFileFromData(IN idFile  VARCHAR(32), IN createdAt DATETIME, IN fileName VARCHAR(100),
                                    IN idOwner VARCHAR(32))
  BEGIN
    INSERT INTO files (file_id, path, created_at, name, owner_id) VALUES (idFile,'future',createdAt,fileName,idOwner);
  END;

CREATE PROCEDURE getFilePathByIdFromSandbox(IN idFile VARCHAR(32))
  BEGIN
    SELECT CONCAT_WS('_', file_id, UNIX_TIMESTAMP(created_at), MD5(name)) as result FROM sandbox_files WHERE file_id = idFile ;
END;

CREATE PROCEDURE deleteFileFromSandBox(IN idFaile VARCHAR(32))
  BEGIN
    DELETE FROM sandbox_files WHERE file_id = idFaile;
  END;
