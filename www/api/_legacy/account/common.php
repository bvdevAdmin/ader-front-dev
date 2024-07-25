<?php
/*
 +=============================================================================
 | 
 | 회원 공통함수 (메일)
 | -------
 |
 | 최초 작성	: 박성혁
 | 최초 작성일	: 2023.06.19
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 |            
 | 
 +=============================================================================
*/

include_once $_CONFIG['PATH']['API'].'_legacy/PHPMailer/class.PHPMailer.php';
include_once $_CONFIG['PATH']['API'].'_legacy/PHPMailer/class.SMTP.php';
include_once $_CONFIG['PATH']['API'].'_legacy/PHPMailer/class.Exception.php';

function commonSendMail($to_id, $title, $content){
    $smtp       = "smtp.gmail.com";
    $from_id 	= "admin@bvdev.co.kr";
    $pass 		= "bvdevadmin!";
    $mail       = new \PHPMailer\PHPMailer\PHPMailer(true);

    $mail->IsSMTP();
    $mail->Host        = $smtp;
    $mail->SMTPAuth    = true;
    $mail->Username    = $from_id;
    $mail->Password    = $pass;
    $mail->SMTPSecure  = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port        = 587;
    $mail->setFrom($from_id, 'ADER');
    $mail->addAddress($to_id, '');
    $mail->isHTML(true);
    $mail->Subject     = $title;
    $mail->Body        = $content;
    $mail->send();
}
function joinMailSet($member_id, $member_name)
{
    $to_id      = $member_id;
	$title 		= "[ADER] ".$member_id." 님 회원가입을 축하 드립니다.";
    $content    = iconv("UTF-8","EUC-KR", '
    <div id=":om" class="ii gt" jslog="20277; u014N:xr6bB; 1:WyIjdGhyZWFkLWY6MTc2ODc0NTI1MDA2NzU5OTM5MSIsbnVsbCxudWxsLG51bGwsbnVsbCxudWxsLG51bGwsbnVsbCxudWxsLG51bGwsbnVsbCxudWxsLG51bGwsW11d; 4:WyIjbXNnLWY6MTc2ODc0NTI1MDA2NzU5OTM5MSIsbnVsbCxbXV0.">
        <div id=":ol" class="a3s aiL ">
            <div class="adM">
            </div>
            <p>
                <br>
            </p>
            <p><span style="color:rgb(37,37,37)">&nbsp;</span><span style="color:rgb(37,37,37)">&nbsp;</span><span
                    style="color:rgb(37,37,37)">&nbsp;</span><span style="color:rgb(37,37,37)">&nbsp;</span></p>
            <table align="center" border="0" cellpadding="0" cellspacing="0" style="border:1px solid rgb(187,192,196)"
                width="700">
                <tbody>
                    <tr>
                        <td style="padding:24px 14px 0px"><span style="color:rgb(37,37,37)">&nbsp;</span><span
                                style="color:rgb(37,37,37)">&nbsp;</span><span
                                style="color:rgb(37,37,37)">&nbsp;</span><span
                                style="color:rgb(37,37,37)">&nbsp;</span><span style="color:rgb(37,37,37)">&nbsp;</span>
                            <span style="color:rgb(37,37,37)">&nbsp;</span><span
                                style="color:rgb(37,37,37)">&nbsp;</span><span
                                style="color:rgb(37,37,37)">&nbsp;</span><span
                                style="color:rgb(37,37,37)">&nbsp;</span><span
                                style="color:rgb(37,37,37)">&nbsp;</span><span style="color:rgb(37,37,37)">&nbsp;</span>
                            <span style="color:rgb(37,37,37)">&nbsp;</span>
                            <table border="0" cellpadding="0" cellspacing="0" width="670">
                                <tbody>

                                    <tr>
                                        <td><img src="https://ci4.googleusercontent.com/proxy/yGE2zJt-Nn7vycmqZ4QR9BoOCxSCuAkTq3ZDLcbvmgBcId4PrwvE7N25Q6pFn3G5gBo1b1fqw7hI7zdKjQ=s0-d-e1-ft#http://bbbtan.cafe24.com/2015fw/csmail.jpg"
                                                class="CToWUd a6T" data-bit="iit" tabindex="0">
                                            <div class="a6S" dir="ltr" style="opacity: 0.01; left: 654.5px; top: 232.5px;">
                                                <div id=":1n6" class="T-I J-J5-Ji aQv T-I-ax7 L3 a5q" role="button"
                                                    tabindex="0" aria-label="첨부파일() 다운로드"
                                                    jslog="91252; u014N:cOuCgd,Kr2w4b,xr6bB; 4:WyIjbXNnLWY6MTc2ODc0NTI1MDA2NzU5OTM5MSIsbnVsbCxbXV0."
                                                    data-tooltip-class="a1V" data-tooltip="다운로드">
                                                    <div class="akn">
                                                        <div class="aSK J-J5-Ji aYr"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>


                                    <tr>
                                        <td
                                            style="padding:50px 0px 0px 10px;color:rgb(57,57,57);line-height:19px;font-family:Gulim;font-size:12px">
                                            <p><span style="color:rgb(37,37,37)"><span style="font-family:Arial"><span
                                                            style="font-family:Tahoma">안녕하세요.&nbsp;</span></span></span><strong><span
                                                        style="color:rgb(37,37,37)"><span style="font-family:Arial"><span
                                                                style="font-family:Tahoma">ADER</span></span></span></strong>
                                                <span style="color:rgb(37,37,37)"><span style="font-family:Arial"><span
                                                            style="font-family:Tahoma">&nbsp;입니다.</span></span></span>
                                            </p><span style="color:rgb(37,37,37)"><span style="font-family:Arial"><span
                                                        style="font-family:Tahoma">&nbsp;</span></span></span>
                                            <p style="margin-top:13px"><strong><span style="color:rgb(37,37,37)"><span
                                                            style="font-family:Arial"><span
                                                                style="font-family:Tahoma">'.$member_name.'('.$member_id.')</span></span></span></strong><span
                                                    style="color:rgb(37,37,37)"><span style="font-family:Arial"><span
                                                            style="font-family:Tahoma">&nbsp;고객님의 회원가입을
                                                            축하드립니다.</span></span></span>
                                                <br><span style="color:rgb(37,37,37)"><span style="font-family:Arial"><span
                                                            style="font-family:Tahoma">&nbsp;회원님의 가입정보는 다음과
                                                            같습니다.</span></span></span>
                                            </p><span style="color:rgb(37,37,37)">&nbsp;</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><span style="color:rgb(37,37,37)">&nbsp;</span><span
                                                style="color:rgb(37,37,37)">&nbsp;</span><span
                                                style="color:rgb(37,37,37)">&nbsp;</span><span
                                                style="color:rgb(37,37,37)">&nbsp;</span><span
                                                style="color:rgb(37,37,37)">&nbsp;</span>
                                            <span style="color:rgb(37,37,37)">&nbsp;</span><span
                                                style="color:rgb(37,37,37)">&nbsp;</span><span
                                                style="color:rgb(37,37,37)">&nbsp;</span><span
                                                style="color:rgb(37,37,37)">&nbsp;</span><span
                                                style="color:rgb(37,37,37)">&nbsp;</span>
                                            <span style="color:rgb(37,37,37)">&nbsp;</span>
                                            <table border="0" cellpadding="0" cellspacing="0"
                                                style="color:rgb(57,57,57);line-height:19px;font-family:Gulim;font-size:12px"
                                                width="670">
                                                <tbody>
                                                    <tr>
                                                        <td height="40"><span style="color:rgb(37,37,37)">&nbsp;</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td><span style="color:rgb(37,37,37)">&nbsp;</span><span
                                                                style="color:rgb(37,37,37)">&nbsp;</span><span
                                                                style="color:rgb(37,37,37)">&nbsp;</span><span
                                                                style="color:rgb(37,37,37)">&nbsp;</span>
                                                            <span style="color:rgb(37,37,37)">&nbsp;</span><span
                                                                style="color:rgb(37,37,37)">&nbsp;</span><span
                                                                style="color:rgb(37,37,37)">&nbsp;</span><span
                                                                style="color:rgb(37,37,37)">&nbsp;</span>
                                                            <table border="0" cellpadding="0" cellspacing="0"
                                                                style="margin:0px 0px 20px" width="100%">
                                                                <tbody>
                                                                    <tr>
                                                                        <td valign="middle" width="19"><span
                                                                                style="color:rgb(37,37,37)"><img alt=""
                                                                                    src="https://ci3.googleusercontent.com/proxy/x5e0HHFk-armO_vC9az1woeBnPiw28Q4W_xvOoTrTSj6vypridOUDY92090DAnM7jtm-lKzYFqJn7-29tdMXgsuAtuRLCduCbtPyIWpXlPPZ=s0-d-e1-ft#http://m-img.cafe24.com/images/template/admin/kr/ico_title.gif"
                                                                                    class="CToWUd" data-bit="iit"></span>
                                                                        </td>
                                                                        <td valign="middle"><strong
                                                                                style="color:rgb(28,28,28);font-family:Gulim;font-size:13px"><span
                                                                                    style="color:rgb(37,37,37);font-family:Tahoma">가입
                                                                                    정보</span></strong></td>
                                                                    </tr>
                                                                </tbody>
                                                            </table><span style="color:rgb(37,37,37)">&nbsp;</span><span
                                                                style="color:rgb(37,37,37)">&nbsp;</span><span
                                                                style="color:rgb(37,37,37)">&nbsp;</span><span
                                                                style="color:rgb(37,37,37)">&nbsp;</span>
                                                            <span style="color:rgb(37,37,37)">&nbsp;</span><span
                                                                style="color:rgb(37,37,37)">&nbsp;</span><span
                                                                style="color:rgb(37,37,37)">&nbsp;</span><span
                                                                style="color:rgb(37,37,37)">&nbsp;</span>
                                                            <table border="0" cellpadding="0" cellspacing="0"
                                                                style="line-height:15px;font-family:Gulim;font-size:12px;border-top-color:rgb(213,213,213);border-top-width:1px;border-top-style:solid"
                                                                width="100%">
                                                                <tbody>
                                                                    <tr>
                                                                        <th align="left" scope="row"
                                                                            style="padding:13px 10px 10px;color:rgb(128,135,141);font-weight:normal;border-right-color:rgb(213,213,213);border-bottom-color:rgb(213,213,213);border-left-color:rgb(213,213,213);border-right-width:1px;border-bottom-width:1px;border-left-width:1px;border-right-style:solid;border-bottom-style:solid;border-left-style:solid;background-color:rgb(255,255,255)"
                                                                            valign="middle" width="22%"><span
                                                                                style="color:rgb(37,37,37);font-family:Tahoma">ID</span>
                                                                        </th>
                                                                        <td align="left"
                                                                            style="padding:13px 10px 10px;color:rgb(57,57,57);border-right-color:rgb(213,213,213);border-bottom-color:rgb(213,213,213);border-right-width:1px;border-bottom-width:1px;border-right-style:solid;border-bottom-style:solid"
                                                                            valign="middle" width="78%"><span
                                                                                style="color:rgb(37,37,37);font-family:Tahoma">'.$member_id.'</span>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table><span style="color:rgb(37,37,37)">&nbsp;</span>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table><span style="color:rgb(37,37,37)">&nbsp;</span><span
                                                style="color:rgb(37,37,37)">&nbsp;</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td
                                            style="padding:30px 0px 60px 10px;color:rgb(57,57,57);line-height:19px;font-family:Gulim;font-size:12px">
                                            <span style="color:rgb(37,37,37)">&nbsp;</span>
                                            <p><span style="color:rgb(37,37,37)"><br></span></p><span
                                                style="color:rgb(37,37,37)"><span
                                                    style="color:rgb(37,37,37)">&nbsp;</span></span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table><span style="color:rgb(37,37,37)"><span style="color:rgb(37,37,37)">&nbsp;</span></span>
                        </td>
                    </tr>
                    <tr>
                        <td
                            style="padding:24px 34px;color:rgb(255,255,255);line-height:18px;font-family:Gulim;font-size:12px;background-color:rgb(255,255,255)">
                            <span style="color:rgb(37,37,37)"><span style="color:rgb(37,37,37)">&nbsp;</span></span>
                            <p><span style="color:rgb(37,37,37)"><span style="color:rgb(37,37,37)">&nbsp;<span
                                            style="font-family:Tahoma">Tel :&nbsp;</span></span></span><strong><span
                                        style="color:rgb(37,37,37)"><span style="color:rgb(37,37,37)"><span
                                                style="font-family:Tahoma">02-792-2232</span></span></span></strong>
                                <span style="color:rgb(37,37,37)"><span style="color:rgb(37,37,37)"><span
                                            style="font-family:Tahoma">&nbsp;| Fax : </span></span></span>
                                <br><span style="color:rgb(37,37,37)"><span style="color:rgb(37,37,37)"><span
                                            style="font-family:Tahoma">&nbsp;04782 서울특별시 성동구 연무장길 53 (성수동2가) 3층
                                            삼영빌딩</span></span></span>
                                <br><span style="color:rgb(37,37,37)"><span style="color:rgb(37,37,37)"><span
                                            style="font-family:Tahoma">&nbsp;개인정보관리책임자 : 정승환 | 사업자 등록번호 [760-87-01757] |
                                            통신판매업 신고 : 제 2021-서울성동-01588호&nbsp;</span></span></span>
                            </p>
                            <span style="color:rgb(37,37,37)"><span style="color:rgb(37,37,37)"><span
                                        style="font-family:Tahoma">&nbsp;</span></span></span>
                            <p><span style="color:rgb(37,37,37)"><span style="color:rgb(37,37,37)"><span
                                            style="font-family:Tahoma">Copyright(c) ADER all rights
                                            reserved.&nbsp;</span></span></span><a href="#m_-6406077948424355335_none"
                                    style="color:rgb(255,255,255);text-decoration:none"><span
                                        style="color:rgb(37,37,37)"><span style="color:rgb(37,37,37)"><span
                                                style="font-family:Tahoma">adererror.com</span></span></span></a></p>
                        </td>
                    </tr>

                </tbody>
            </table>
            <hr>
            <hr>
            <p>
                <br>
            </p>
            <div class="yj6qo"></div>
            <div class="adL">
            </div>
        </div>
    </div>
    ');
	commonSendMail($to_id, $title, $content);
}

function findPwMailSet($member_id, $member_name, $tmp_pw)
{
    $to_id      = $member_id;
    $title 		= "[ADER] ".$member_id." 님 임시 비밀번호가 생성되었습니다.";
    $content    = iconv("UTF-8","EUC-KR", '
    <div id=":p6" class="ii gt" jslog="20277; u014N:xr6bB; 1:WyIjdGhyZWFkLWY6MTc2ODc0NTM5NTA4MDk1NTE1NSIsbnVsbCxudWxsLG51bGwsbnVsbCxudWxsLG51bGwsbnVsbCxudWxsLG51bGwsbnVsbCxudWxsLG51bGwsW11d; 4:WyIjbXNnLWY6MTc2ODc0NTM5NTA4MDk1NTE1NSIsbnVsbCxbXV0.">
        <div id=":p8" class="a3s aiL ">
            <div class="adM">
                <span style="color:rgb(37,37,37)">
                </span><span style="color:rgb(37,37,37)">
                </span><span style="color:rgb(37,37,37)"></span><span style="color:rgb(37,37,37)">
                </span><span style="color:rgb(37,37,37)">
                </span>
            </div>
            <table width="700" align="center" style="border:1px solid rgb(187,192,196)" border="0" cellspacing="0"
                cellpadding="0">
                <tbody>
                    <tr>
                        <td style="padding:24px 14px 0px">
                            <span style="color:rgb(37,37,37)">
                            </span><span style="color:rgb(37,37,37)">
                            </span><span style="color:rgb(37,37,37)"></span><span style="color:rgb(37,37,37)">
                            </span><span style="color:rgb(37,37,37)">
                            </span><span style="color:rgb(37,37,37)">
                            </span><span style="color:rgb(37,37,37)">
                            </span><span style="color:rgb(37,37,37)"></span><span style="color:rgb(37,37,37)">
                            </span><span style="color:rgb(37,37,37)">
                            </span><span style="color:rgb(37,37,37)">
                            </span><span style="color:rgb(37,37,37)">
                            </span><span style="color:rgb(37,37,37)"></span><span style="color:rgb(37,37,37)">
                            </span><span style="color:rgb(37,37,37)">
                            </span>
                            <table width="670" border="0" cellspacing="0" cellpadding="0">
                                <tbody>

                                    <tr>
                                        <td>
                                            <img src="https://ci4.googleusercontent.com/proxy/yGE2zJt-Nn7vycmqZ4QR9BoOCxSCuAkTq3ZDLcbvmgBcId4PrwvE7N25Q6pFn3G5gBo1b1fqw7hI7zdKjQ=s0-d-e1-ft#http://bbbtan.cafe24.com/2015fw/csmail.jpg"
                                                class="CToWUd a6T" data-bit="iit" tabindex="0">
                                            <div class="a6S" dir="ltr" style="opacity: 0.01; left: 654.5px; top: 135px;">
                                                <div id=":1n5" class="T-I J-J5-Ji aQv T-I-ax7 L3 a5q" role="button"
                                                    tabindex="0" aria-label="첨부파일() 다운로드"
                                                    jslog="91252; u014N:cOuCgd,Kr2w4b,xr6bB; 4:WyIjbXNnLWY6MTc2ODc0NTM5NTA4MDk1NTE1NSIsbnVsbCxbXV0."
                                                    data-tooltip-class="a1V" data-tooltip="다운로드">
                                                    <div class="akn">
                                                        <div class="aSK J-J5-Ji aYr"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>


                                    <tr>
                                        <td
                                            style="padding:50px 0px 0px 10px;color:rgb(57,57,57);line-height:19px;font-family:Gulim;font-size:12px">
                                            <p><span style="color:rgb(37,37,37)"><span style="font-family:Tahoma">안녕하세요.
                                                    </span></span><strong><span style="color:rgb(37,37,37)"><span
                                                            style="font-family:Tahoma">ADER</span></span></strong><span
                                                    style="color:rgb(37,37,37)"><span style="font-family:Tahoma">
                                                        입니다.</span></span><br><span style="color:rgb(37,37,37)"><span
                                                        style="font-family:Tahoma">
                                                        저희 ADER을 방문해 주셔서 감사드립니다.</span></span></p><span
                                                style="color:rgb(37,37,37)"><span style="font-family:Tahoma">
                                                </span></span>
                                            <p style="margin-top:13px"><strong><span style="color:rgb(37,37,37)"><span
                                                            style="font-family:Tahoma">'.$member_name.'('.$member_id.')</span></span></strong><span
                                                    style="color:rgb(37,37,37)"><span style="font-family:Tahoma"> 고객님의 가입정보는
                                                        다음과 같습니다.</span></span></p><span style="color:rgb(37,37,37)">
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><span style="color:rgb(37,37,37)">
                                            </span><span style="color:rgb(37,37,37)"></span><span
                                                style="color:rgb(37,37,37)">
                                            </span><span style="color:rgb(37,37,37)">
                                            </span><span style="color:rgb(37,37,37)">
                                            </span><span style="color:rgb(37,37,37)"></span><span
                                                style="color:rgb(37,37,37)">
                                            </span><span style="color:rgb(37,37,37)"></span><span
                                                style="color:rgb(37,37,37)">
                                            </span><span style="color:rgb(37,37,37)">
                                            </span><span style="color:rgb(37,37,37)">
                                            </span><span style="color:rgb(37,37,37)">
                                            </span><span style="color:rgb(37,37,37)"></span><span
                                                style="color:rgb(37,37,37)">
                                            </span><span style="color:rgb(37,37,37)">
                                            </span>
                                            <table width="670"
                                                style="color:rgb(57,57,57);line-height:19px;font-family:Gulim;font-size:12px"
                                                border="0" cellspacing="0" cellpadding="0">
                                                <tbody>
                                                    <tr>
                                                        <td height="40"><span style="color:rgb(37,37,37)">&nbsp;</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td><span style="color:rgb(37,37,37)">
                                                            </span><span style="color:rgb(37,37,37)">
                                                            </span><span style="color:rgb(37,37,37)">
                                                            </span><span style="color:rgb(37,37,37)">
                                                            </span><span style="color:rgb(37,37,37)">
                                                            </span><span style="color:rgb(37,37,37)">
                                                            </span><span style="color:rgb(37,37,37)">
                                                            </span><span style="color:rgb(37,37,37)">
                                                            </span>
                                                            <table width="100%" style="margin:0px 0px 20px" border="0"
                                                                cellspacing="0" cellpadding="0">
                                                                <tbody>
                                                                    <tr>
                                                                        <td width="19" valign="middle"><span
                                                                                style="color:rgb(37,37,37)"><img alt=""
                                                                                    src="https://ci3.googleusercontent.com/proxy/x5e0HHFk-armO_vC9az1woeBnPiw28Q4W_xvOoTrTSj6vypridOUDY92090DAnM7jtm-lKzYFqJn7-29tdMXgsuAtuRLCduCbtPyIWpXlPPZ=s0-d-e1-ft#http://m-img.cafe24.com/images/template/admin/kr/ico_title.gif"
                                                                                    class="CToWUd" data-bit="iit"></span>
                                                                        </td>
                                                                        <td valign="middle"><strong
                                                                                style="color:rgb(28,28,28);font-family:Gulim;font-size:13px"><span
                                                                                    style="color:rgb(37,37,37)"><span
                                                                                        style="font-family:Tahoma">가입
                                                                                        정보</span></span></strong></td>
                                                                    </tr>
                                                                </tbody>
                                                            </table><span style="color:rgb(37,37,37)">
                                                            </span><span style="color:rgb(37,37,37)">
                                                            </span><span style="color:rgb(37,37,37)">
                                                            </span><span style="color:rgb(37,37,37)">
                                                            </span><span style="color:rgb(37,37,37)">
                                                            </span><span style="color:rgb(37,37,37)">
                                                            </span><span style="color:rgb(37,37,37)">
                                                            </span><span style="color:rgb(37,37,37)">
                                                            </span><span style="color:rgb(37,37,37)">
                                                            </span><span style="color:rgb(37,37,37)">
                                                            </span>
                                                            <table width="100%"
                                                                style="line-height:15px;font-family:Gulim;font-size:12px;border-top-color:rgb(213,213,213);border-top-width:1px;border-top-style:solid"
                                                                border="0" cellspacing="0" cellpadding="0">
                                                                <tbody>
                                                                    <tr>
                                                                        <th width="22%" align="left" valign="middle"
                                                                            style="padding:13px 10px 10px;color:rgb(128,135,141);font-weight:normal;border-right-color:rgb(213,213,213);border-bottom-color:rgb(213,213,213);border-left-color:rgb(213,213,213);border-right-width:1px;border-bottom-width:1px;border-left-width:1px;border-right-style:solid;border-bottom-style:solid;border-left-style:solid;background-color:rgb(255,255,255)"
                                                                            scope="row"><span
                                                                                style="color:rgb(37,37,37);font-family:Tahoma">아이디</span>
                                                                        </th>
                                                                        <td width="28%" align="left" valign="middle"
                                                                            style="padding:13px 10px 10px;color:rgb(57,57,57);border-right-color:rgb(213,213,213);border-bottom-color:rgb(213,213,213);border-right-width:1px;border-bottom-width:1px;border-right-style:solid;border-bottom-style:solid">
                                                                            <span
                                                                                style="color:rgb(37,37,37);font-family:Tahoma">'.$member_id.'</span>
                                                                        </td>
                                                                        <th width="22%" align="left" valign="middle"
                                                                            style="padding:13px 10px 10px;color:rgb(128,135,141);font-weight:normal;border-right-color:rgb(213,213,213);border-bottom-color:rgb(213,213,213);border-right-width:1px;border-bottom-width:1px;border-right-style:solid;border-bottom-style:solid;background-color:rgb(255,255,255)"
                                                                            scope="row"><span
                                                                                style="color:rgb(37,37,37);font-family:Tahoma">임시비밀번호</span>
                                                                        </th>
                                                                        <td width="28%" align="left" valign="middle"
                                                                            style="padding:13px 10px 10px;color:rgb(57,57,57);border-right-color:rgb(213,213,213);border-bottom-color:rgb(213,213,213);border-right-width:1px;border-bottom-width:1px;border-right-style:solid;border-bottom-style:solid">
                                                                            <span
                                                                                style="color:rgb(37,37,37);font-family:Tahoma">'.$tmp_pw.'</span></td>
                                                                    </tr>
                                                                </tbody>
                                                            </table><span style="color:rgb(37,37,37)">
                                                            </span>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table><span style="color:rgb(37,37,37)">
                                            </span><span style="color:rgb(37,37,37)"></span><span
                                                style="color:rgb(37,37,37)">
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td
                                            style="padding:30px 0px 60px 10px;color:rgb(57,57,57);line-height:19px;font-family:Gulim;font-size:12px">
                                            <span style="color:rgb(37,37,37)">
                                            </span>
                                            <p><span style="color:rgb(37,37,37)"><span style="font-family:Tahoma">회원가입 시 등록한
                                                        정보를 수정하려면, [MY PAGE]에서 변경하실 수 있습니다.</span></span></p><span
                                                style="color:rgb(37,37,37)"><span style="font-family:Tahoma">
                                                </span></span>
                                            <p style="margin-top:13px"><span style="color:rgb(37,37,37)"><span
                                                        style="font-family:Tahoma">언제든지 </span></span><strong><span
                                                        style="color:rgb(37,37,37)"><span
                                                            style="font-family:Tahoma">'.$member_name.'('.$member_id.')</span></span></strong><span
                                                    style="color:rgb(37,37,37)"><span style="font-family:Tahoma"> 고객님의 ADER
                                                        방문을 기다리겠습니다.</span></span></p><span style="color:rgb(37,37,37)">
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table><span style="color:rgb(37,37,37)">
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td
                            style="padding:24px 34px;color:rgb(255,255,255);line-height:18px;font-family:Gulim;font-size:12px;background-color:rgb(255,255,255)">
                            <span style="color:rgb(37,37,37)">
                            </span>
                            <p><span style="color:rgb(37,37,37)">
                                    <span style="font-family:Tahoma">Tel : </span></span><strong><span
                                        style="color:rgb(37,37,37)"><span
                                            style="font-family:Tahoma">02-792-2232</span></span></strong><span
                                    style="color:rgb(37,37,37)"><span style="font-family:Tahoma"> | Fax :
                                    </span></span><br><span style="color:rgb(37,37,37)"><span style="font-family:Tahoma">
                                        04782 서울특별시 성동구 연무장길 53 (성수동2가) 3층 삼영빌딩</span></span><br><span
                                    style="color:rgb(37,37,37)"><span style="font-family:Tahoma">
                                        개인정보관리책임자 : 정승환 | 사업자 등록번호 [760-87-01757] | 통신판매업 신고 : 제 2021-서울성동-01588호
                                    </span></span></p><span style="color:rgb(37,37,37)"><span style="font-family:Tahoma">
                                </span></span>
                            <p><span style="color:rgb(37,37,37)"><span style="font-family:Tahoma">Copyright(c) ADER all
                                        rights reserved. </span></span><a
                                    style="color:rgb(255,255,255);text-decoration:none"
                                    href="#m_6355716870242187175_none"><span style="color:rgb(37,37,37)"><span
                                            style="font-family:Tahoma">adererror.com</span></span></a></p>
                        </td>
                    </tr>

                </tbody>
            </table>
            <div class="yj6qo"></div>
            <div class="adL">

            </div>
        </div>
    </div>
    ');
	commonSendMail($to_id, $title, $content);
}

/* 생일 바우처 발급처리 */
function issueBirthVoucher($db,$country,$member_idx,$param_start_date,$param_end_date) {
	if ($member_idx != null && $member_idx > 0) {
		$select_voucher_mst_sql = "
			SELECT
				VM.IDX					AS VOUCHER_IDX
			FROM
				VOUCHER_MST VM
			WHERE
				VOUCHER_TYPE = 'BR'
			AND
				VM.COUNTRY = '".$country."'
		";
		
		$db->query($select_voucher_mst_sql);
	
		$last_id = 0;
		
		foreach($db->fetch() as $data) {
			$voucher_idx = $data['VOUCHER_IDX'];
			
			$voucher_issue_code = makeVoucherCode();
			
			if (!empty($voucher_idx)) {
				/* 생일바우처 발급 이력 체크 */
				$issue_cnt = $db->count(
					"VOUCHER_ISSUE VI",
					"
						VI.VOUCHER_IDX = ".$voucher_idx." AND
						VI.MEMBER_IDX = ".$member_idx." AND
						VI.CREATE_YEAR = DATE_FORMAT(NOW(),'%Y') AND
						VI.CREATE_MONTH = DATE_FORMAT(NOW(),'%m')
					"
				);
				
				if ($issue_cnt == 0) {
					/* 생일바우처 발급 이력이 존재하지 않는 경우 */
					$insert_voucher_issue_sql = "
						INSERT INTO
							VOUCHER_ISSUE
						(
							COUNTRY,
							VOUCHER_IDX,
							VOUCHER_ISSUE_CODE,
							
							VOUCHER_ADD_DATE,
							USABLE_START_DATE,
							USABLE_END_DATE,
							
							CREATE_YEAR,
							CREATE_MONTH,
							MEMBER_IDX,
							MEMBER_ID,
							CREATER,
							UPDATER
						)
						SELECT 
							'".$country."',
							".$voucher_idx.",
							'".$voucher_issue_code."',
							
							NOW(),
							'".$param_start_date."',
							'".$param_end_date."',
							
							DATE_FORMAT(NOW(), '%Y'),
							DATE_FORMAT(NOW(), '%m'),
							MB.IDX,
							MB.MEMBER_ID,
							'system',
							'system'
						FROM
							MEMBER_".$country." MB
						WHERE
							MB.IDX = ".$member_idx."
					";
					
					$db->query($insert_voucher_issue_sql);
					
					$voucher_issue_idx = $db->last_id();
					if (!empty($voucher_issue_idx)) {
						/* [바우처 발행]테이블 갱신처리 */
						$update_voucher_mst_sql = "
							UPDATE 
								VOUCHER_MST
							SET 
								TOT_ISSUE_NUM = (
									SELECT 
										COUNT(0)
									FROM
										VOUCHER_ISSUE
									WHERE
										VOUCHER_IDX = ".$voucher_idx."
								)
							WHERE
								IDX = ".$voucher_idx."
						";
						
						$db->query($update_voucher_mst_sql);
					}
				}
			}
		}
	}

	if($last_id > 0){
		return true;
	}
	else{
		return false;
	}
}
function makeVoucherCode(){
	$micro_now      = microtime(true);
	$micro_now_dex  = str_replace('.','',$micro_now);
	$micro_now_hex  = dechex($micro_now_dex);

	return strtoupper($micro_now_hex);
}
