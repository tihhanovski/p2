<?php

	class WarehouseStateRecalculator extends CronTask
	{
		public $interval = 60;

		function run()
		{
			//app()->setLocale(DEFAULT_LOCALE);
			$cnt = 0;

			$whs = app()->queryAsArray("select whId as id from (
				select distinct whSrcId as whId from whmv
				union select distinct whDstId as whId from whmv) x
				where whId > 1", DB_FETCHMODE_OBJECT);
			foreach ($whs as $wh)
			{
				$whId = $wh->id;
				$sts = app()->queryAsArray("select id, nq as q, nt as t from(
						select a.id, sum(m.q) as nq, round(sum(m.q * m.cost), 6) as nt, s.quantity as oq, s.total as ot
						from article a left join
						(select articleId, quantity * if(whDstId = $whId, 1, -1) as q, cost
						from whmv where whSrcId = $whId or whDstId = $whId) m on m.articleId = a.id
						left join whstate s on s.whId = $whId and s.articleId = a.id
						group by a.id, s.quantity, s.total
						) x where coalesce(nq, 0) <> coalesce(oq, 0) and coalesce(nt, 0) <> coalesce(ot, 0)
						", DB_FETCHMODE_OBJECT);
				foreach ($sts as $st)
				{
					$aId = $st->id;
					$q = $st->q;
					$t = $st->t;
					app()->query("insert into whstate (whId, articleId, quantity, total)
							values($whId, $aId, $q, $t)
							on duplicate key update quantity = $q, total = $t");
					$cnt++;
				}
			}
			$this->log("recalculated: $cnt");
		}

	}