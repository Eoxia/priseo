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

ALTER TABLE llx_priseo_competitorprice ADD INDEX idx_priseo_competitorprice_rowid (rowid);
ALTER TABLE llx_priseo_competitorprice ADD INDEX idx_priseo_competitorprice_ref (ref);
ALTER TABLE llx_priseo_competitorprice ADD INDEX idx_priseo_competitorprice_status (status);
ALTER TABLE llx_priseo_competitorprice ADD INDEX idx_priseo_competitorprice_fk_soc (fk_soc);
ALTER TABLE llx_priseo_competitorprice ADD INDEX idx_priseo_competitorprice_fk_product (fk_product);
ALTER TABLE llx_priseo_competitorprice ADD INDEX idx_priseo_competitorprice_fk_user_creat (fk_user_creat);
ALTER TABLE llx_priseo_competitorprice ADD CONSTRAINT llx_priseo_competitorprice_fk_soc FOREIGN KEY (fk_soc) REFERENCES llx_societe(rowid);
ALTER TABLE llx_priseo_competitorprice ADD CONSTRAINT llx_priseo_competitorprice_fk_product FOREIGN KEY (fk_product) REFERENCES llx_product(rowid);
ALTER TABLE llx_priseo_competitorprice ADD CONSTRAINT llx_priseo_competitorprice_fk_user_creat FOREIGN KEY (fk_user_creat) REFERENCES llx_user(rowid);