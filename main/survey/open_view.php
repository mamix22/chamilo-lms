<?php
$langFile = 'survey';

require ('../inc/global.inc.php');
require_once (api_get_path(LIBRARY_PATH)."/surveymanager.lib.php");
$status = surveymanager::get_status();
if($status==5)
{
api_protect_admin_script();
}
$cidReq = $_REQUEST['cidReq'];
require_once (api_get_path(LIBRARY_PATH)."/course.lib.php");
$table_category = Database :: get_main_table(MAIN_CATEGORY_TABLE);
$table_survey = Database :: get_course_table('survey');
$table_group =  Database :: get_course_table('survey_group');
$table_question = Database :: get_course_table('questions');
$tool_name = get_lang('ViewQuestions');
$header2 = get_lang('GroupName');
$header3 = get_lang('Type');
$ques_id = $_GET['qid'];
$gname=surveymanager::ques_id_group_name($ques_id);
$ques_type = $_GET['qtype'];
$sql = "SELECT * FROM $db_name.questions where qid='$ques_id'";
$res = api_sql_query($sql);
$obj = mysql_fetch_object($res);	
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>White Template</title>
<link href="../survey/css_white/style.css" rel="stylesheet" type="text/css">
<style type="text/css">
<!--
body {
	margin-left: 0px;
	margin-top: 0px;
	margin-right: 0px;
	margin-bottom: 0px;
}
.style5 {font-size: 12px; font-weight: bold; font-family: Arial, Helvetica, sans-serif;}
-->
</style>
</head>

<body>
<table width="100%" cellpadding="0" cellspacing="1" bordercolor="#000000" bgcolor="#000000">
  <tr>
    <td width="66%" bgcolor="#FFFFFF" class="bg-3">&nbsp;&nbsp;<span class="text">My Portal &gt; Course Home &gt; Survey</span></td>
    <td width="34%" align="right" bgcolor="#FFFFFF" class="bg-3"><span class="text"></span>&nbsp;</td>
  </tr>
  <tr bgcolor="#FFFFFF">
    <td colspan="2" align="center" valign="top"><br>
      <br>
      <table width="600" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td width="23" height="21"><img src="../survey/images_white/top-1.gif" width="23" height="21"></td>
          <td height="21" background="../survey/images_white/top-2.gif">&nbsp;</td>
          <td width="20" height="21"><img src="../survey/images_white/top-3.gif" width="20" height="21"></td>
        </tr>
        <tr>
		<td height="39" background="../survey/images_white/left.gif">&nbsp;</td>
        <td><strong>Question: </strong><br><? echo $obj->caption;;?><br><br><strong>Answer: </strong><br>
		<TEXTAREA  style="WIDTH: 100%" name="defaultext" rows=3 cols=60>
        </TEXTAREA> 	
		</td>
        <td background="../survey/images_white/right.gif">&nbsp;</td>
		</tr>
        <tr>
          <td background="../survey/images_white/left.gif">&nbsp;</td>
          <td><p>&nbsp;</p>
              </td>
          <td background="../survey/images_white/right.gif">&nbsp;</td>
        </tr>
        <tr>
          <td><img src="../survey/images_white/bottom-1.gif" width="23" height="21"></td>
          <td background="../survey/images_white/bottom-2.gif">&nbsp;</td>
          <td><img src="../survey/images_white/bottom-3.gif" width="20" height="21"></td>
        </tr>
      </table>
      <p><br>
        <br>
      </p>
    <p>&nbsp;</p>
    <p>&nbsp;           </p></td>
  </tr>
  <tr align="right" bgcolor="#FFFFFF">
    <td height="30" align="left" class="bg-4">&nbsp;&nbsp;<span class="text">Manager : </span><span class="text-2">user admin</span> </td>
    <td height="30" class="bg-4"><span class="text">Platform</span> <span class="text-2">Dokeos 1.6.3</span> <span class="text">&copy; 2006&nbsp;&nbsp;</span></td>
  </tr>
</table>
</body>
</html>
