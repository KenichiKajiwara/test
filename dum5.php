<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/conf/config.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/dbAccess.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/common_utilities.php");

$dbh = new dbAccess();

$goback_month = 6;

$lender_id = 101;
// 抽出対象範囲（未指定なら最初から）
if ($goback_month == NULL) {
	$select_start_date = strtotime("2016-04-01 00:00:00");
} else {
	$select_start_date = strtotime("-" . $goback_month . " month");
}
$select_start_date = date("Y-m-d H:i:s", $select_start_date);

$news_array = array();				// 集計用の２次元配列を初期化

	// --- キャッシュバックの情報を集計
	$sql = "select deposit_detail, regist_date from account where status = :status and lender_id = :lender_id ";
	$sql .= " and deposit_division = :deposit_division and deposit_detail <> :csvnyukin and deposit_detail <> :shikin ";
	$sql .= " and regist_date >= :select_start_date ; ";
	$param = array(
			":status" => REC_STATUS_YUKO,
			":lender_id" => $lender_id,
			":deposit_division" => "1",		// 入金　（キャッシュバックを抽出）
			":csvnyukin" => "csv入金",
			":shikin" => "投資資金",
			":select_start_date" => $select_start_date
	);
	$res = $dbh->query($sql, $param);
	while($row = $res->fetch(PDO::FETCH_ASSOC)) {
		$news_array[] = array(
				"title" => $row['deposit_detail'],
				"date" => $row['regist_date'],
				"kbn" => "1"		// キャッシュバック
		);
	}

	// --- 貸付実行の情報を集計
	$sql = "select loan_fund_id, loan_fund_name, investment_term_from from loan_fund as lf, lender_investment as li  ";
	$sql .= " where lf.status = :lf_status and li.status = :li_status and investment_term_from >= :select_start_date ";
	$sql .= " and lf.id = loan_fund_id and is_concluded = :is_concluded and lender_id = :lender_id ";
	$sql .= " group by loan_fund_id ; ";
	$param = array(
			":lf_status" => REC_STATUS_YUKO,
			":li_status" => REC_STATUS_YUKO,
			":lender_id" => $lender_id,
			":is_concluded" => "1",		// 成立
			":select_start_date" => $select_start_date
	);
	$res = $dbh->query($sql, $param);
	while($row = $res->fetch(PDO::FETCH_ASSOC)) {
		$news_array[] = array(
				"title" => $row['loan_fund_name'],
				"date" => $row['investment_term_from'],
				"kbn" => "2",		// 貸付実行
				"loan_fund_id" => $row['loan_fund_id']
		);
	}

	// --- 分配・償還の情報を集計
	$sql = "select loan_fund_id, loan_fund_name, lender_interest_date, investment_principal, interest_income ";
	$sql .= " from loan_fund as lf, lender_interest as li  ";
	$sql .= " where lf.status = :lf_status and li.status = :li_status and lender_interest_date >= :select_start_date ";
	$sql .= " and lf.id = loan_fund_id and lender_id = :lender_id ";
	$sql .= " group by loan_fund_id ; ";
	$param = array(
			":lf_status" => REC_STATUS_YUKO,
			":li_status" => REC_STATUS_YUKO,
			":lender_id" => $lender_id,
			":select_start_date" => $select_start_date
	);
	$res = $dbh->query($sql, $param);
	while($row = $res->fetch(PDO::FETCH_ASSOC)) {
		if(intval($row['interest_income']) > 0) {
			$news_array[] = array(
					"title" => $row['loan_fund_name'],
					"date" => $row['lender_interest_date'],
					"kbn" => "3",		// 貸付実行
					"loan_fund_id" => $row['loan_fund_id']
			);
		}
		if(intval($row['investment_principal']) > 0) {
			$news_array[] = array(
					"title" => $row['loan_fund_name'],
					"date" => $row['lender_interest_date'],
					"kbn" => "4",		// 貸付実行
					"loan_fund_id" => $row['loan_fund_id']
			);
		}
	}

	// 日付でソート
	sortArrayByKey( $news_array, 'date', SORT_DESC );

	echo('<table border="1">');
	foreach($news_array as $line) {
		echo('<tr><td>'.$line['date'].'</td><td>'.$line['kbn'].'</td><td>'.$line['title'].'</td></tr>');
	}
	echo('</table>');

	function sortArrayByKey( &$array, $sortKey, $sortType = SORT_ASC ) {
		$tmpArray = array();
		foreach ( $array as $key => $row ) {
			$tmpArray[$key] = $row[$sortKey];
		}
		array_multisort( $tmpArray, $sortType, $array );
		unset( $tmpArray );
	}




