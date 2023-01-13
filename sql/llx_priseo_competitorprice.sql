-- Copyright (C) 2022      Florian HENRY <floria.henry@scopen.fr>
-- Copyright (C) 2022-2023 EOXIA         <dev@eoxia.com>
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see https://www.gnu.org/licenses/.

CREATE TABLE llx_priseo_competitorprice(
	rowid           integer AUTO_INCREMENT PRIMARY KEY NOT NULL,
	ref             varchar(128) NOT NULL,
    entity          integer DEFAULT 1 NOT NULL,
	date_creation   datetime NOT NULL,
	tms             timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status          integer NOT NULL,
	label           varchar(255),
	amount_ht       double DEFAULT NULL,
	amount_ttc      double DEFAULT NULL,
	vat             double DEFAULT NULL,
	url_competitor  varchar(1024),
    competitor_date datetime,
	fk_product      integer NOT NULL,
	fk_soc          integer NOT NULL,
	fk_user_creat   integer NOT NULL,
	fk_user_modif   integer
) ENGINE=innodb;