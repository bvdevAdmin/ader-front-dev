/* 직원 정보 */
CREATE TABLE `ACCOUNTS` (
  `IDX`					INT(10) UNSIGNED								NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '인덱스키',

  `ID`					VARCHAR(50)				DEFAULT ''				NOT NULL COMMENT '아이디',
  `EMPLOYEE_ID`			VARCHAR(20)				DEFAULT ''				NOT NULL COMMENT '사원번호',

  `NAME`				VARCHAR(40)				DEFAULT ''				NOT NULL COMMENT '이름',
  `NAME_LAST`			VARCHAR(40)				DEFAULT ''				NOT NULL COMMENT '성',
  `NICK`				VARCHAR(40)				DEFAULT ''				NOT NULL COMMENT '별명',
  `TEAM_NO`				INT(3) UNSIGNED			DEFAULT 0				NOT NULL COMMENT '팀 인덱스',
  `ROLE_NO`				INT(3) UNSIGNED 		DEFAULT 0				NOT NULL COMMENT '역할 인덱스',
  `EMAIL`				VARCHAR(50)				DEFAULT ''				NOT NULL COMMENT '보조 이메일',
  `PW`					VARCHAR(50)				DEFAULT ''				NOT NULL COMMENT '비밀번호',
  `PW_DATE`				DATETIME										NULL COMMENT '비밀번호 설정일',
  `PW_HISTORY`      	LONGTEXT										NOT NULL COMMENT '비밀번호 과거 내역',
  `PROFILE_IMAGE`		VARCHAR(200)			DEFAULT ''				NOT NULL COMMENT '프로필 사진',
  `BIRTHDAY`			DATE											NULL COMMENT '생일',

  `MOBILE`				VARCHAR(11)				DEFAULT ''				NOT NULL COMMENT '연락처',
  `MOBILE_VERTIFY`		DATE											NULL COMMENT '연락처 인증',
  `PERMISSION_NO`		INT(3) UNSIGNED 		DEFAULT 0				NOT NULL COMMENT '권한 인덱스',
  `PERMISSION_DEFINE`	LONGTEXT										NOT NULL COMMENT '개별 권한 정의',

  `REMARK`				VARCHAR(2000)			DEFAULT ''				NOT NULL COMMENT '비고',

  `SESSION_ID`			VARCHAR(50)				DEFAULT ''				NOT NULL COMMENT '로그인 세션값',
  `FCM`					VARCHAR(100)			DEFAULT ''				NOT NULL COMMENT '앱 세션값',
  `IP`					VARCHAR(15)				DEFAULT '0.0.0.0'		NOT NULL COMMENT '사용IP',

  `JOIN_DATE`			DATETIME				DEFAULT NOW()			NOT NULL COMMENT '생성일',
  `LOGIN_DATE`			DATETIME										NULL COMMENT '로그인 일자',
  `LOGIN_CNT`			INT(10) UNSIGNED		DEFAULT 0				NOT NULL COMMENT '로그인 횟수',
  `STATUS`				ENUM('정상','정지','퇴사')	DEFAULT '정상'			NOT NULL COMMENT '현재 상태',
  
  CHECK(JSON_VALID(`PW_HISTORY`)),
  CHECK(JSON_VALID(`PERMISSION_DEFINE`)),
  INDEX `INDEX_ACCOUNTS` (`ID`,`EMPLOYEE_ID`,`PW`,`NAME`,`MOBILE`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='계정 정보';
INSERT INTO ACCOUNTS (ID,NAME,NICK,PW,PW_DATE,PW_HISTORY,PERMISSION_DEFINE) VALUES ('root','최고관리자','최고관리자','1111',NOW(),'[]','{"is_root_administrator":true}');


/* 팀 */
CREATE TABLE `ACCOUNTS_TEAM` (
  `IDX`					INT(10) UNSIGNED								NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '인덱스키',

  `FATHER_NO`			INT(3) UNSIGNED			DEFAULT 0				NOT NULL COMMENT '상위 팀',
  `CODE`				VARCHAR(10)				DEFAULT ''				NOT NULL COMMENT '코드명',
  `TITLE`				VARCHAR(40)				DEFAULT ''				NOT NULL COMMENT '팀명칭',
  `REMARK`				VARCHAR(2000)			DEFAULT ''				NOT NULL COMMENT '비고',

  INDEX `INDEX_ACCOUNTS_TEAM` (`FATHER_NO`,`CODE`,`TITLE`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='팀 정보';

/* 역할 */
CREATE TABLE `ACCOUNTS_ROLE` (
  `IDX`					INT(10) UNSIGNED								NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '인덱스키',

  `TEAM_NO`				INT(3) UNSIGNED			DEFAULT 0				NOT NULL COMMENT '팀 인덱스',
  `PERMISSION_NO`		INT(3) UNSIGNED 		DEFAULT 0				NOT NULL COMMENT '지정 권한 인덱스',
  `CODE`				VARCHAR(10)				DEFAULT ''				NOT NULL COMMENT '코드명',
  `TITLE`				VARCHAR(40)				DEFAULT ''				NOT NULL COMMENT '역할명칭',
  `REMARK`				VARCHAR(2000)			DEFAULT ''				NOT NULL COMMENT '비고',

  INDEX `INDEX_ACCOUNTS_ROLE` (`TEAM_NO`,`CODE`,`TITLE`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='역할 정보';

/* 권한 설정 */
CREATE TABLE `ACCOUNTS_PERMISSION_DEF` (
  `IDX`					INT(10) UNSIGNED								NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '인덱스키',

  `TITLE`				VARCHAR(40)				DEFAULT ''				NOT NULL COMMENT '권한명',
  `DEFINE`				LONGTEXT										NOT NULL COMMENT '권한 정의',
  `REMARK`				VARCHAR(2000)			DEFAULT ''				NOT NULL COMMENT '비고',

  CHECK(JSON_VALID('DEFINE')),
  INDEX `INDEX_ACCOUNTS_PERMISSION_DEF` (`TITLE`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='권한 설정';







/* 게임 기록 */
CREATE TABLE `GAME_RECORD` (
  `IDX`					INT(10) UNSIGNED								NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '인덱스키',

  `KEYWORD`				VARCHAR(100)			DEFAULT ''				NOT NULL COMMENT '소재명',
  `REMARK`				VARCHAR(2000)			DEFAULT ''				NOT NULL COMMENT '비고',

  `CREATED_DATE`			DATETIME				DEFAULT NOW()			NOT NULL COMMENT '생성일',
  `MODIFIED_DATE`			DATETIME				ON UPDATE CURRENT_TIMESTAMP		NOT NULL COMMENT '취종 인식일',

  INDEX `INDEX_MATERIAL` (`KEYWORD`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='소재';

/* 프로모션 */
CREATE TABLE `AI_VISION_LOG` (
  `IDX`					INT(10) UNSIGNED									NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '인덱스키',

  `LABEL`				VARCHAR(100)		DEFAULT ''						NOT NULL COMMENT '라벨링',
  `IMG_URL`				VARCHAR(100)		DEFAULT ''						NOT NULL COMMENT '인식 이미지 URL',
  `AI`					VARCHAR(10)			DEFAULT ''						NOT NULL COMMENT 'AI서비스',
  `TOKEN`				INT(10) UNSIGNED	DEFAULT 0						NOT NULL COMMENT '사용 토큰',
  `ID`					VARCHAR(100)		DEFAULT ''						NOT NULL COMMENT '회원ID',
  `GPS`					POINT												NULL COMMENT 'GPS좌표',
  `SENT`				LONGTEXT											NOT NULL COMMENT '프롬프트',
  `RETURN`				LONGTEXT											NOT NULL COMMENT '응답',
  `REMARK`				VARCHAR(2000)		DEFAULT ''						NOT NULL COMMENT '비고',

  `CREATED_DATE`		DATETIME			DEFAULT NOW()					NOT NULL COMMENT '생성일',

  CHECK(JSON_VALID(`SENT`)),
  CHECK(JSON_VALID(`RETURN`)),
  INDEX `INDEX_AI_VISION_LOG` (`LABEL`,`GPS`,`ID`,`TOKEN`,`AI`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='AI 로그';

