<?

include "sql_connection_info.php";


$query_array = array();
$conn = mysqli_connect($sqlAddr, $sqlId, $sqlPw, $sqlBlogDb);
$conn->set_charset("utf8mb4");

$query_array["user_list_table_query"] =
	"CREATE TABLE `user_list` (
		`user_index` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`user_id` TINYTEXT NOT NULL COLLATE 'utf8mb4_general_ci',
		`user_pw` TEXT NOT NULL COMMENT 'sha256' COLLATE 'utf8mb4_general_ci',
		`user_level` TINYINT(3) UNSIGNED NOT NULL DEFAULT '4' COMMENT '0:root\n1:Admin\n2:poster\n3:member\n4:visitor',
		`user_state` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0:normal\n1:ban',
		`user_first_action_datetime` DATETIME NOT NULL DEFAULT current_timestamp(),
		`user_last_action_datetime` DATETIME NOT NULL DEFAULT current_timestamp(),
		`user_posting_count` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
		`user_posting_limit` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
		PRIMARY KEY (`user_index`) USING BTREE,
		UNIQUE INDEX `user_index` (`user_index`) USING BTREE,
		UNIQUE INDEX `user_id` (`user_id`) USING HASH
	)
	COLLATE='utf8mb4_general_ci'
	ENGINE=InnoDB
	AUTO_INCREMENT=4
	;";

$query_array["category_list_table_query"] =
	"CREATE TABLE `category_list` (
		`category_index` TINYINT(3) UNSIGNED NOT NULL AUTO_INCREMENT,
		`category_name` TEXT NOT NULL COLLATE 'utf8mb4_general_ci',
		`category_order` TINYINT(3) UNSIGNED NOT NULL,
		`category_read_level` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'follow user_level',
		`category_write_level` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'follow user_level',
		PRIMARY KEY (`category_index`) USING BTREE,
		UNIQUE INDEX `category_order` (`category_order`) USING BTREE,
		UNIQUE INDEX `category_index` (`category_index`) USING BTREE,
		UNIQUE INDEX `category_name` (`category_name`) USING HASH,
		INDEX `category_read_level` (`category_read_level`) USING BTREE,
		INDEX `category_write_level` (`category_write_level`) USING BTREE
	)
	COLLATE='utf8mb4_general_ci'
	ENGINE=InnoDB
	AUTO_INCREMENT=9
	;";

$query_array["posting_list_table_query"] = 
	"CREATE TABLE `posting_list` (
		`posting_index` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
		`user_index` INT(10) UNSIGNED NOT NULL DEFAULT '0',
		`category_index` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
		`posting_state` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0:normal\n1:disabled',
		`posting_first_post_datetime` DATETIME NOT NULL DEFAULT current_timestamp(),
		`posting_last_edit_datetime` DATETIME NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
		`posting_read_cnt` INT(10) UNSIGNED NOT NULL DEFAULT '0',
		`posting_title` TINYTEXT NOT NULL DEFAULT '' COLLATE 'utf8mb4_general_ci',
		`posting_thumbnail` TINYTEXT NOT NULL DEFAULT '' COLLATE 'utf8mb4_general_ci',
		`posting_summary` TEXT NOT NULL DEFAULT '' COLLATE 'utf8mb4_general_ci',
		`posting_content` MEDIUMTEXT NOT NULL DEFAULT '' COLLATE 'utf8mb4_general_ci',
		PRIMARY KEY (`posting_index`) USING BTREE,
		UNIQUE INDEX `posting_index` (`posting_index`) USING BTREE,
		INDEX `FK__user_list` (`user_index`) USING BTREE,
		INDEX `posting_title` (`posting_title`(255)) USING BTREE,
		INDEX `FK__category_list` (`category_index`) USING BTREE,
		CONSTRAINT `FK__user_list` FOREIGN KEY (`user_index`) REFERENCES `user_list` (`user_index`) ON UPDATE CASCADE ON DELETE CASCADE,
		CONSTRAINT `FK_category_list` FOREIGN KEY (`category_index`) REFERENCES `category_list` (`category_index`) ON UPDATE CASCADE ON DELETE CASCADE
	)
	COLLATE='utf8mb4_general_ci'
	ENGINE=InnoDB
	AUTO_INCREMENT=20
	;";

$query_array["visitor_count_table_query"] = 
	"CREATE TABLE `weekly_visitors` (
		`year_week` INT(10) UNSIGNED NOT NULL,
		`visit_count` INT(10) UNSIGNED NOT NULL,
		PRIMARY KEY (`year_week`) USING BTREE
	)
	COLLATE='utf8mb4_general_ci'
	ENGINE=InnoDB
	;";

$query_array["get_full_posting_and_increase_cnt_procedure_query"] = 
	"CREATE DEFINER=`bae4969`@`localhost` PROCEDURE `get_full_posting_and_increase_cnt`(
		IN `input_user_level` TINYINT,
		IN `input_user_index` INT,
		IN `input_posting_index` INT,
		IN `input_is_increase_cnt` INT
	)
	LANGUAGE SQL
	NOT DETERMINISTIC
	CONTAINS SQL
	SQL SECURITY DEFINER
	COMMENT ''
	BEGIN
		DECLARE post_exists INT DEFAULT 0;
	
		-- 조건에 맞는 데이터 선택 및 존재 여부 확인
		SELECT 
			COUNT(*) INTO post_exists
		FROM posting_list AS P
		JOIN category_list AS C ON P.category_index = C.category_index
		WHERE C.category_read_level >= input_user_level
		AND P.posting_index = input_posting_index
		AND (
			input_user_level <= 1
			OR (input_user_index < 0 AND P.posting_state = 0)
			OR (input_user_index >= 0 AND (P.posting_state = 0 OR P.user_index = input_user_index))
		);
	
		IF post_exists > 0 THEN
			-- 값 증가
			IF input_is_increase_cnt > 0 THEN
				UPDATE posting_list
				SET posting_read_cnt = posting_read_cnt + 1
				WHERE posting_index = input_posting_index;
			END IF;
	
			-- 결과 반환
			SELECT 
				P.posting_index, P.user_index, P.category_index, P.posting_state, 
				P.posting_first_post_datetime, P.posting_last_edit_datetime, P.posting_read_cnt,
				P.posting_title, P.posting_content 
			FROM posting_list AS P
			JOIN category_list AS C ON P.category_index = C.category_index
			WHERE P.posting_index = input_posting_index;
		END IF;
	END";

foreach ($query_array as $key => $sql_query) {
    $result = mysqli_query($conn, $sql_query);
    mysqli_fetch_assoc($result);
	print("Executed ".$key." query\n");
}
