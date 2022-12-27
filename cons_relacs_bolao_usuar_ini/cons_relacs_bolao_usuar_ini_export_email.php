<?php
   include_once('cons_relacs_bolao_usuar_ini_session.php');
   session_start();
    if (!function_exists("NM_is_utf8"))
    {
        include_once("../_lib/lib/php/nm_utf8.php");
    }
    $exportEmail = new exportEmail_cons_relacs_bolao_usuar_ini(); 
   
class exportEmail_cons_relacs_bolao_usuar_ini
{
    function __construct()
    {
       $this->proc_ajax = false;
       if (isset($_POST['script_case_init']))
       {
           $this->sc_init      = filter_input(INPUT_POST, 'script_case_init', FILTER_SANITIZE_NUMBER_INT);
           $this->path_img     = filter_input(INPUT_POST, 'path_img', FILTER_SANITIZE_STRING);
           $this->path_btn     = filter_input(INPUT_POST, 'path_btn', FILTER_SANITIZE_STRING);
           $this->embbed       = filter_input(INPUT_POST, 'embbed_groupby', FILTER_SANITIZE_STRING);
           $this->tbar_pos     = filter_input(INPUT_POST, 'toolbar_pos', FILTER_SANITIZE_SPECIAL_CHARS);
           $this->sType        = filter_input(INPUT_POST, 'sType', FILTER_SANITIZE_STRING);
           $this->sAdd         = filter_input(INPUT_POST, 'sAdd', FILTER_SANITIZE_STRING);
       }
       elseif (isset($_GET['script_case_init']))
       {
           $this->sc_init      = filter_input(INPUT_GET, 'script_case_init', FILTER_SANITIZE_NUMBER_INT);
           $this->path_img     = filter_input(INPUT_GET, 'path_img', FILTER_SANITIZE_STRING);
           $this->path_btn     = filter_input(INPUT_GET, 'path_btn', FILTER_SANITIZE_STRING);
           $this->embbed       = filter_input(INPUT_GET, 'embbed_groupby', FILTER_SANITIZE_STRING);
           $this->tbar_pos     = filter_input(INPUT_GET, 'toolbar_pos', FILTER_SANITIZE_SPECIAL_CHARS);
           $this->sType        = filter_input(INPUT_GET, 'sType', FILTER_SANITIZE_STRING);
           $this->sAdd         = filter_input(INPUT_GET, 'sAdd', FILTER_SANITIZE_STRING);
       }
       $this->sAdd        = str_replace('__E__', '&', $this->sAdd);
       if (isset($_POST['ajax_ctrl']) && $_POST['ajax_ctrl'] == "proc_ajax")
       {
           $this->proc_ajax = true;
       }
       $this->ajax_return = array();
       if (isset($_POST['fsel_ok']) && $_POST['fsel_ok'] == "OK")
       {
           $this->exportEmailSend();
       }
       else
       {
           if ($this->embbed)
           {
               ob_start();
               $this->exportEmailShow();
               $Temp = ob_get_clean();
               echo NM_charset_to_utf8($Temp);
           }
           else
           {
               $this->exportEmailShow();
           }
       }
       exit;
       
    }


    function getJsonEmails($str_email)
    {
      $str_return = '';

      $arr_emails = trim($str_email);
      $arr_emails = explode(';', $arr_emails);
      $arr_new    = array();
      foreach($arr_emails as $email)
      {
        $email = trim($email);
        if(!empty($email))
        {
          $arr_new[] = $email;
        }
      }

      if(!empty($arr_new))
      {
        $str_return = json_encode($arr_new);
      }

      return $str_return;
    }

    function exportEmailSend()
    {
        include_once('cons_relacs_bolao_usuar_ini_sc_mail_image.php');
        include_once('../_lib/lib/php/nm_api.php');

        $arr_to  = SC_Mail_Address_array_api(implode(';', json_decode($_POST['html_export_email_to'])), 'to');
        $arr_cc   = array();
        $arr_bcc  = array();

        $arr_attach = array();
        if(isset($_POST['str_file']) && is_file($_POST['str_file']))
        {
          $arr_attach = array($_POST['str_file']);
        }

        $return = sc_send_mail_api([
                          'profile' => 'grp__NM__bolao_email',
                          
                          'message' => [
                                        'html'          => nl2br($_POST['html_export_email_email']),
                                        'text'          => strip_tags($_POST['html_export_email_email']),
                                        'to'            => $arr_to,
                                        'subject'       => $_POST['html_export_email_subject'],
                                        'attachments'   => $arr_attach,
                                       ]
                        ]);
        if($return == false)
        {
            $STR_lang    = (isset($_SESSION['scriptcase']['str_lang']) && !empty($_SESSION['scriptcase']['str_lang'])) ? $_SESSION['scriptcase']['str_lang'] : 'pt_br';
            $NM_arq_lang = '../_lib/lang/' . $STR_lang . '.lang.php';
            $this->Nm_lang = array();
            if (is_file($NM_arq_lang)){
                include_once($NM_arq_lang);
            }
            $__api_name = 'bolao_email';
            echo $this->Nm_lang['lang_errm_api_nfnd'] . $__api_name;
            return 'error';
        }
        return 'ok';
    }
    function exportEmailShow()
    {
       if ($this->proc_ajax)
       {
           ob_start();
       }
       $size = 10;
       $STR_lang    = (isset($_SESSION['scriptcase']['str_lang']) && !empty($_SESSION['scriptcase']['str_lang'])) ? $_SESSION['scriptcase']['str_lang'] : "pt_br";
       $NM_arq_lang = "../_lib/lang/" . $STR_lang . ".lang.php";
       $this->Nm_lang = array();
       if (is_file($NM_arq_lang))
       {
           include_once($NM_arq_lang);
       }
       $_SESSION['scriptcase']['charset']  = "UTF-8";
       foreach ($this->Nm_lang as $ind => $dados)
       {
          if ($_SESSION['scriptcase']['charset'] != "UTF-8" && NM_is_utf8($ind))
          {
              $ind = sc_convert_encoding($ind, $_SESSION['scriptcase']['charset'], "UTF-8");
              $this->Nm_lang[$ind] = $dados;
          }
          if ($_SESSION['scriptcase']['charset'] != "UTF-8" && NM_is_utf8($dados))
          {
              $this->Nm_lang[$ind] = sc_convert_encoding($dados, $_SESSION['scriptcase']['charset'], "UTF-8");
          }
       }
       $str_schema_all = (isset($_SESSION['scriptcase']['str_schema_all']) && !empty($_SESSION['scriptcase']['str_schema_all'])) ? $_SESSION['scriptcase']['str_schema_all'] : "Sc9_Meadow/Sc9_Meadow";
       include("../_lib/css/" . $str_schema_all . "_grid.php");
       $Str_btn_grid = trim($str_button) . "/" . trim($str_button) . $_SESSION['scriptcase']['reg_conf']['css_dir'] . ".php";
      include("../_lib/buttons/" . $Str_btn_grid);
       if (!function_exists("nmButtonOutput"))
       {
           include_once("../_lib/lib/php/nm_gp_config_btn.php");
       }
   if (!$this->embbed)
   {
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
            "http://www.w3.org/TR/1999/REC-html401-19991224/loose.dtd">
<HTML<?php echo $_SESSION['scriptcase']['reg_conf']['html_dir'] ?>>
<HEAD>
 <TITLE><?php echo "Adicionar Participantes"; ?></TITLE>
 <META http-equiv="Content-Type" content="text/html; charset=<?php echo $_SESSION['scriptcase']['charset_html'] ?>" />
<?php
if ($_SESSION['scriptcase']['proc_mobile'])
{
?>
   <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
<?php
}
?>
 <META http-equiv="Expires" content="Fri, Jan 01 1900 00:00:00 GMT"/>
 <META http-equiv="Last-Modified" content="<?php echo gmdate("D, d M Y H:i:s"); ?> GMT"/>
 <META http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate"/>
 <META http-equiv="Cache-Control" content="post-check=0, pre-check=0"/>
 <META http-equiv="Pragma" content="no-cache"/>
 <link rel="shortcut icon" href="../_lib/img/grp__NM__ico__NM__favicon.ico">
 <link rel="stylesheet" type="text/css" href="../_lib/css/<?php echo $_SESSION['scriptcase']['css_popup'] ?>" /> 
 <link rel="stylesheet" type="text/css" href="../_lib/css/<?php echo $_SESSION['scriptcase']['css_popup_dir'] ?>" /> 
 <link rel="stylesheet" type="text/css" href="../_lib/css/<?php echo $_SESSION['scriptcase']['css_popup_div'] ?>" /> 
 <link rel="stylesheet" type="text/css" href="../_lib/css/<?php echo $_SESSION['scriptcase']['css_popup_div_dir'] ?>" /> 
 <link rel="stylesheet" type="text/css" href="../_lib/buttons/<?php echo $_SESSION['scriptcase']['css_btn_popup'] ?>" /> 
 <link rel="stylesheet" type="text/css" href="<?php echo $_SESSION['sc_session']['path_third'] ?>/bootstrap/css/bootstrap.min.css" /> 
 <link rel="stylesheet" type="text/css" href="<?php echo $_SESSION['sc_session']['path_third'] ?>/font-awesome/css/all.min.css" /> 
 <link rel="stylesheet" type="text/css" href="<?php echo $_SESSION['sc_session']['path_third'] ?>/jquery_plugin/pierresh-multiple-emails/multiple-emails.css" /> 
</HEAD>
<BODY class="scGridPage" style="margin: 0px; overflow-x: hidden">
<script language="javascript" type="text/javascript" src="../_lib/lib/js/jquery-3.6.0.min.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $_SESSION['sc_session']['path_third'] ?>/jquery/js/jquery-ui.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $_SESSION['sc_session']['path_third'] ?>/jquery_plugin/touch_punch/jquery.ui.touch-punch.min.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $_SESSION['sc_session']['path_third'] ?>/bootstrap/js/bootstrap.min.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $_SESSION['sc_session']['path_third'] ?>/jquery_plugin/pierresh-multiple-emails/multiple-emails.js"></script>
<?php
   }
$str_parent = '';
$str_close  = "$('#id_html_export_email').hide();";
if (!$this->embbed)
{
    $str_parent = 'self.parent.';
    $str_close  = "self.parent.tb_remove();";
}
?>
            <style>
            .multiple_emails-ul li{ display:inline-block !important; }
            .multiple_emails-ul li a{ float:none !important; }
            #id_div_export_email_to .multiple_emails-container{min-height:68px;}
            </style>
            <script>
              $( document ).ready(function() {
                $('#id_html_export_email_to').multiple_emails({position: "bottom"});
                $('#id_html_export_email_cc').multiple_emails({position: "bottom"});
                $('#id_html_export_email_bcc').multiple_emails({position: "bottom"});

                $('#id_html_export_email_to').change( function(){
                  ajusta_window_exportEmail();
                });
                $('#id_html_export_email_cc').change( function(){
                  ajusta_window_exportEmail();
                });
                $('#id_html_export_email_bcc').change( function(){
                  ajusta_window_exportEmail();
                });
              });
            </script>
            <script>
            function html_export_email_show()
            {
                $("#id_html_export_email").show();
            }

            function html_export_email_export()
            {
              email_to = JSON.parse($('#id_html_export_email_to').val());
              if(email_to.length == 0)
              {
                $('#id_html_export_email_to').parent().find('.multiple_emails-input').focus();
              }
              else
              {
                <?php echo $str_parent; ?>nmAjaxProcOn();
                <?php echo $str_parent; ?>ajax_export('<?php echo $this->sType; ?>', '<?php echo $this->sAdd; ?>', html_export_email_send, false);
              }
            }

            function html_export_email_send()
            {
                $.ajax({
                    type: 'POST',
                    url: 'cons_relacs_bolao_usuar_ini_export_email.php',
                    data: $('#idExportEmailForm').serialize() + '&str_file=' + <?php echo $str_parent; ?>strPath + '&str_title='+ <?php echo $str_parent; ?>strTitle,
                    success: function(data)
                    {
                      <?php echo $str_parent; ?>nmAjaxProcOff();
                      if(data != '')
                      {
                        alert(data);
                        html_export_email_cancel();
                      }
                      else
                      {
                        $('#id_title_sent').show();
                        $('#id_body_exportemail').hide();
                        $('#id_title_exportemail').hide();
                        $('#id_tr_toolbar').hide();
                        ajusta_window_exportEmail();

                        setTimeout("html_export_email_cancel()", 5000);
                      }
                    }
                });
            }

            function html_export_email_cancel()
            {
                <?php echo $str_close; ?>
            }
        </script><FORM name="exportEmailForm" id="idExportEmailForm" method="POST">
  <INPUT type="hidden" name="script_case_init" id="id_script_case_init_export_email" value="<?php echo NM_encode_input($this->sc_init); ?>"> 
  <INPUT type="hidden" name="path_img" id="id_path_img_export_email" value="<?php echo NM_encode_input($this->path_img); ?>"> 
  <INPUT type="hidden" name="path_btn" id="id_path_btn_export_email" value="<?php echo NM_encode_input($this->path_btn); ?>"> 
  <INPUT type="hidden" name="fsel_ok" id="id_fsel_ok_export_email" value="OK"> 
<?php
if ($this->embbed)
{
    echo "<div class='scAppDivMoldura'>";
    echo "<table id=\"main_table\" style=\"width: 700px;\" cellspacing=0 cellpadding=0>";
}
elseif ($_SESSION['scriptcase']['reg_conf']['html_dir'] == " DIR='RTL'")
{
    echo "<table id=\"main_table\" style=\"width: 700px;position: relative; top: 20px; right: 20px\">";
}
else
{
    echo "<table id=\"main_table\" style=\"width: 700px;position: relative; top: 20px; left: 20px\">";
}
?>
<?php
if (!$this->embbed)
{
?>
<tr>
<td>
<div class="scGridBorder">
<table width='100%' cellspacing=0 cellpadding=0>
<?php
}
?>
 <tr id='id_title_exportemail'>
  <td class="<?php echo ($this->embbed)? 'scAppDivHeader scAppDivHeaderText':'scGridLabelVert'; ?>">
   <?php echo $this->Nm_lang['lang_othr_grid_export_email_titl']; ?>
  </td>
 </tr>
 <tr>
  <td class="scAppDivContent css_scAppDivContentText">
   <table style="border-width: 0; border-collapse: collapse; width:100%;" cellspacing=0 cellpadding=0>
    <tr>
     <td style="vertical-align: top">
       <div id='id_title_sent' class='scExportEmailLabelSent' style='display:none' onclick='html_export_email_cancel();'>
         <?php echo $this->Nm_lang['lang_othr_grid_export_email_sent']; ?>
       </div>
       <div id='id_body_exportemail' class='scExportEmailTable'>

           <div id='id_div_export_email_to' class='scExportEmailDiv'><span class='scExportEmailLabel'><?php echo $this->Nm_lang['lang_othr_grid_export_email_to']; ?></span><br /><input id="id_html_export_email_to" name="html_export_email_to" size=70 class='scExportEmailInput' value='<?php echo $this->getJsonEmails(""); ?>' type="text"><br /></div>

           <div id='id_div_export_email_cc' class='scExportEmailDiv'  style='display:none' ><span class='scExportEmailLabel'><?php echo $this->Nm_lang['lang_othr_grid_export_email_cc']; ?></span><br /><input id="id_html_export_email_cc" name="html_export_email_cc" size=70 class='scExportEmailInput' value='<?php echo $this->getJsonEmails(""); ?>' type="text"><br /></div>

           <div id='id_div_export_email_bcc' class='scExportEmailDiv'  style='display:none' ><span class='scExportEmailLabel'><?php echo $this->Nm_lang['lang_othr_grid_export_email_bcc']; ?></span><br /><input id="id_html_export_email_bcc" name="html_export_email_bcc" size=70 class='scExportEmailInput' value='<?php echo $this->getJsonEmails(""); ?>' type="text"><br /></div>

           <div id='id_div_export_email_subject' class='scExportEmailDiv'><span class='scExportEmailLabel'><?php echo $this->Nm_lang['lang_othr_grid_export_email_subject']; ?></span><br /><input id="id_html_export_email_subject" name="html_export_email_subject" size=70 class='scExportEmailInput' value="<?php echo "Participe do bolão " . sprintf($_SESSION['bolao_nome'], '') . " "; ?>" type="text"><br /></div>

           <div id='id_div_export_email_email' class='scExportEmailDiv'><span class='scExportEmailLabel'><?php echo $this->Nm_lang['lang_othr_grid_export_email_email']; ?></span><br /><textarea id="id_html_export_email_email" rows=7 cols=70 class='scExportEmailInput' name="html_export_email_email"><?php echo preg_replace('/<br\s?\/?>/i', "
", "Este é um convite para participar do " . sprintf($_SESSION['bolao_nome'], $this->Nm_lang['lang_othr_grid_export_email_type_' . $this->sType]) . ".
clique aqui e participe!"); ?></textarea></div>       </div>
     </td>
    </tr>
   </table>
  </td>
  </tr>
   <tr id='id_tr_toolbar'><td class="<?php echo ($this->embbed)? 'scAppDivToolbar':'scGridToolbar'; ?>" align='center'>
   <?php echo nmButtonOutput($this->arr_buttons, "bexportemail", "html_export_email_export()", "html_export_email_export()", "f_sel_sub", "", "", "", "absmiddle", "", "0px", $this->path_btn, "", "", "", "", "", "only_text", "text_right", "", "", "", "", "", "", "");
?>
  &nbsp;&nbsp;&nbsp;
   <?php echo nmButtonOutput($this->arr_buttons, "bsair", "html_export_email_cancel('" . NM_encode_input($this->tbar_pos) . "')", "html_export_email_cancel('" . NM_encode_input($this->tbar_pos) . "')", "Bsair", "", "", "", "absmiddle", "", "0px", $this->path_btn, "", "", "", "", "", "only_text", "text_right", "", "", "", "", "", "", "");
?>
<?php
if (!$this->embbed)
{
?>
 </table>
 </div>
 </td>
 </tr>
<?php
}
?>
 </table>
<?php
if ($this->embbed)
{
?>
    </div>
<?php
}
?>
</FORM>
<?php
   if (!$this->embbed)
   {
?>
<script language="javascript"> 
var bFixed = false;
function ajusta_window_exportEmail()
{
  var mt = $(document.getElementById("main_table"));
  if (0 == mt.width() || 0 == mt.height())
  {
    setTimeout("ajusta_window_exportEmail()", 50);
    return;
  }
  else if(!bFixed)
  {
    bFixed = true;
    if (navigator.userAgent.indexOf("Chrome/") > 0)
    {
      strMaxHeight = Math.min(($(window.parent).height()-80), mt.height());
      strMaxWidth  = Math.min(($(window.parent).width()-40), mt.width());
      $('#main_table').width(strMaxWidth);
      self.parent.tb_resize(strMaxHeight + 40, mt.width() + 40);
      setTimeout("ajusta_window_exportEmail()", 50);
      return;
    }
  }
  strMaxHeight = Math.min(($(window.parent).height()-80), mt.height());
  strMaxWidth  = Math.min(($(window.parent).width()-40), mt.width());
  $('#main_table').width(strMaxWidth);
  self.parent.tb_resize(strMaxHeight + 40, mt.width() + 40);
}
$( document ).ready(function() {
  ajusta_window_exportEmail();
});
</script>
<script>
  ajusta_window_exportEmail();
</script>
</BODY>
</HTML>
<?php
   }
   if ($this->proc_ajax)
   {
       ob_end_clean();
       $oJson = new Services_JSON();
       echo $oJson->encode($this->ajax_return);
       exit;
   }
}
}
