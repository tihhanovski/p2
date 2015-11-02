<?php

	const SQL_COMBO_VAT = "select id, name from vat where closed = 0 order by id";
	const SQL_COMBO_COMPANYGROUP = "select id, name from companygroup where closed = 0 order by name";
