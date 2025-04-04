<%@page import="java.util.Date"%>
<%@page import="java.text.SimpleDateFormat"%>
<%@page import="com.dreamsecurity.json.JSONException"%>
<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%@ page import="java.io.*" %>
<%@ page import="java.net.HttpURLConnection" %>
<%@ page import="java.net.URL" %>
<%@ page import="java.net.URLEncoder" %>
<%@ page import="java.net.URLDecoder" %>
<%@ page import="com.dreamsecurity.json.JSONObject" %>
<%@ page import="com.dreamsecurity.mobileOK.mobileOKKeyManager" %>
<%@ page import="com.dreamsecurity.mobileOK.MobileOKException" %>

<%!
/* 1. 본인확인 인증결과 MOKConfirm API 또는 MOKResult API 요청 URL */
//private final String MOK_CONFIRM_URL = "https://cert-dir.mobile-ok.com/agent/v1/auth/call";  // 운영
private final String MOK_CONFIRM_URL = "https://scert-dir.mobile-ok.com/agent/v1/auth/call"; // 개발

    /* 재시도 버튼 클릭시 이동 JSP (mobileOK-Request JSP)*/
    private final String MOK_RESULT_JSP = "./mok_api_result.jsp";
    /* 처음페이지 버튼 클릭시 이동 JSP (mobileOK-GetToken JSP)*/
    private final String MOK_GET_TOKEN_JSP = "./mok_api_gettoken.jsp";
    /* 재시도 버튼 클릭시 이동 JSP (mobileOK-Call JSP)*/
    private final String MOK_CALL_JSP = "./mok_ars_call.jsp";    
%>

<%
/* 2. 본인확인 키파일을 통한 비밀키 설정 */
String resultData = "";

    try {
        String MOKCallData = request.getParameter("MOKCallData");
        String encryptMOKToken = request.getParameter("encryptMOKToken");
        String arsOtpNumber = request.getParameter("arsOtpNumber");

        if ((MOKCallData == null || MOKCallData.equals("")) && (encryptMOKToken == null || encryptMOKToken.equals(""))) {
            throw new MobileOKException("-1|본인확인 요청 MOKToken이 없습니다.");
        }

        if(!(MOKCallData == null || MOKCallData.equals(""))) {
            MOKCallData = URLDecoder.decode(MOKCallData, "UTF-8");
            resultData = mobileOK_api_call(MOKCallData, session, request);
        } else if(!(encryptMOKToken == null || encryptMOKToken.equals(""))) {           
            resultData = re_mobileOK_api_call(encryptMOKToken, arsOtpNumber, request.getParameter("publicKey"),  session, request);
        }

    } catch (MobileOKException e) {
        resultData = setErrorMsg(null, e.getMessage());
    }
%>

<%!
/* 본인확인 API 콜요청 예제 함수 */
public String mobileOK_api_call(String MOKCallData, HttpSession session, HttpServletRequest request) throws MobileOKException {
/* 3. 본인확인 검증요청 입력정보 설정 (아래 MOKConfirmRequestToJsonString() 참조) */

        JSONObject MOKCallDataJson = new JSONObject(MOKCallData);

        String MOKCallRequest = MOKCallToJsonString(MOKCallDataJson.getString("encryptMOKToken"));

        String arsOtpNumber = MOKCallDataJson.optString("arsOtpNumber", "");

        /* 4. 본인확인 콜결과 확인 요청 */
        String MOKCallResponse = sendPost(MOK_CONFIRM_URL, MOKCallRequest);

        /* 5. 본인확인 검증요청 API에서 이용할 데이터 설정 */
        JSONObject MOKConfirmData = new JSONObject(MOKCallResponse);
        MOKConfirmData.put("publicKey", MOKCallDataJson.getString("publicKey"));
        MOKConfirmData.put("arsOtpNumber", arsOtpNumber);

        if (!"2000".equals(MOKConfirmData.getString("resultCode"))) {
            return setErrorMsg(MOKConfirmData.getString("resultCode"), MOKConfirmData.getString("resultMsg"));
        } else {
            return MOKConfirmData.toString();
        }
    }

    /* 본인확인 API 전화재요청 예제 함수 */
    public String re_mobileOK_api_call(String encryptMOKToken, String arsOtpNumber, String publicKey, HttpSession session, HttpServletRequest request) throws MobileOKException {
        /* 3. 본인확인 검증요청 입력정보 설정 (아래 MOKConfirmRequestToJsonString() 참조) */        

        /* 3. 본인확인 검증요청 입력정보 설정 (아래 MOKConfirmRequestToJsonString() 참조) */        
        String MOKConfrimRequest = MOKCallToJsonString(encryptMOKToken);

        /* 4. 본인확인 인증결과 확인 요청 */
        String MOKCallResponse = sendPost(MOK_CONFIRM_URL, MOKConfrimRequest);

        JSONObject MOKConfirmData = new JSONObject(MOKCallResponse);
        MOKConfirmData.put("publicKey", publicKey);
        MOKConfirmData.put("arsOtpNumber", arsOtpNumber);

        if (!"2000".equals(MOKConfirmData.getString("resultCode"))) {
            return setErrorMsg(MOKConfirmData.getString("resultCode"), MOKConfirmData.getString("resultMsg"));
        } else {
            return MOKConfirmData.toString();
        }
    }

    /* 본인확인-API 콜요청 정보 */
    public String MOKCallToJsonString(
            String encryptMOKToken
        ) throws MobileOKException {        
        JSONObject MOKconfirmRequestJson = new JSONObject();
        MOKconfirmRequestJson.put("encryptMOKToken", encryptMOKToken);

        return MOKconfirmRequestJson.toString();
    }

    /* 본인확인 서버 통신 예제 함수 */
    public String sendPost(String dest, String jsonData) throws MobileOKException {
        HttpURLConnection connection = null;
        DataOutputStream dataOutputStream = null;
        BufferedReader bufferedReader = null;

        try {
            URL url = new URL(dest);
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("POST");
            connection.setRequestProperty("Content-Type", "application/json;charset=UTF-8");
            connection.setDoOutput(true);

            dataOutputStream = new DataOutputStream(connection.getOutputStream());
            dataOutputStream.write(jsonData.getBytes("UTF-8"));

            bufferedReader = new BufferedReader(new InputStreamReader(connection.getInputStream()));
            StringBuffer responseData = new StringBuffer();
            String info;
            while ((info = bufferedReader.readLine()) != null) {
                responseData.append(info);
            }
            return responseData.toString();
        } catch (FileNotFoundException e) {
            throw new MobileOKException("-5|MOK_CONFIRM_URL을 확인해주세요.");
        } catch (JSONException e) {
            throw new MobileOKException("JSON PARSER ERROR");
        } catch(IOException e) {
            throw new MobileOKException("통신 오류");
        } finally {
            try {
                if (bufferedReader != null) {
                    bufferedReader.close();
                }

                if (dataOutputStream != null) {
                    dataOutputStream.close();
                }

                if (connection != null) {
                    connection.disconnect();
                }
            } catch (Exception e) {
                throw new MobileOKException("connection close ERROR");
            }
        }
    }

    private String setErrorMsg(String resultCode , String errorMsg) {
        JSONObject errorJson = new JSONObject();

        if(resultCode != null) {
            errorJson.put("resultCode", resultCode);
        }

        errorJson.put("resultMsg", errorMsg);

        return errorJson.toString();
    }
%>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>mok_ars_call</title>
<script>
    const MOKAuthRequestJson = decodeURIComponent('<%= resultData %>');
    const MOKAuthRequestJsonObject = JSON.parse(MOKAuthRequestJson);

    document.addEventListener("DOMContentLoaded", function () {
        if (MOKAuthRequestJsonObject.resultCode != '2000') {
            /* 오류발생시 */
            window.alert(MOKAuthRequestJsonObject.resultCode + ', ' + MOKAuthRequestJsonObject.resultMsg);                
        } else {
            /* 정장작동시 */
            document.getElementById("MOKConfirmData").value = encodeURIComponent(MOKAuthRequestJson);
            document.getElementById("encryptMOKToken").value = MOKAuthRequestJsonObject.encryptMOKToken;
            document.getElementById("publicKey").value = MOKAuthRequestJsonObject.publicKey;
            document.getElementById("arsOtpNumber").value = MOKAuthRequestJsonObject.arsOtpNumber;
            document.getElementById("authNumber").innerText = MOKAuthRequestJsonObject.arsOtpNumber;
        }
    });
</script>
</head>
<body>
    <div>인증번호 : <span id="authNumber"></span></div>

    <form action='<%= MOK_RESULT_JSP %>' method="post">
        <input type="hidden" id="MOKConfirmData" name="MOKConfirmData" value='' />
        <input type="submit" value="검증" style="width:160px; height:30px; margin-right:5px;" />
        <a href='<%= MOK_GET_TOKEN_JSP %>'> 
            <input type="button" style="width:160px; height:30px; margin-left:5px; margin-bottom:15px;" value='취소' />
        </a>
    </form>
    <form action='<%= MOK_CALL_JSP %>' method="post">
        <input type="hidden" id="encryptMOKToken" name="encryptMOKToken" value='' />
        <input type="hidden" id="arsOtpNumber" name="arsOtpNumber" value='' />
        <input type="hidden" id="publicKey" name="publicKey" value='' />
        <input type="submit" value="전화 재시도" style="width:160px; height:30px; margin-right:5px;" />      
    </form>

</body>
</html>
