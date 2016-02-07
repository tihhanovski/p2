<?php

	const SQL_COMBO_WAREHOUSE = "select id, name from warehouse where closed = 0 order by name";
	const SQL_COMBO_WAREHOUSE_NOVIRTUAL = "select id, name from warehouse where closed = 0 and id > 1 order by name";
	const SQL_COMBO_WHMVTYPE = "select id, name from whmvtype order by id";

	const SQL_COMBO_UNIT = "select id, name from unit where closed = 0 order by name";
	const SQL_COMBO_ARTICLETYPE = "select id, name from articletype order by id";
	const SQL_COMBO_CUSTOMER = "select id, name from company where customer = 1 and closed = 0 order by name";
	const SQL_COMBO_COMPANY = "select id, name from company where closed = 0 order by name";
	const SQL_COMBO_ARTICLE = "select id, concat(code, '   :   ', name) from article where closed = 0 order by code, name";

	const SQL_AUTOCOMPLETE_ARTICLE_ALL = "select concat(code, '   :    ', name) from article where closed = 0 order by code desc";
	const SQL_COMBO_WHMV_MODIFIER = "select id, name from whmvmodifier order by name";
	const SQL_COMBO_MBE_WAREHOUSE = "select id, concat(code, ' : ', name) from warehouses where closed = 0 order by name";
