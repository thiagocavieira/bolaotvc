<?php

class cons_jogos_brasil_rtf
{
   var $Db;
   var $Erro;
   var $Ini;
   var $Lookup;
   var $nm_data;
   var $Texto_tag;
   var $Arquivo;
   var $Tit_doc;
   var $sc_proc_grid; 
   var $NM_cmp_hidden = array();

   //---- 
   function __construct()
   {
      $this->nm_data   = new nm_data("pt_br");
      $this->Texto_tag = "";
   }

   //---- 
   function monta_rtf()
   {
      $this->inicializa_vars();
      $this->gera_texto_tag();
      $this->grava_arquivo_rtf();
      if ($this->Ini->sc_export_ajax)
      {
          $this->Arr_result['file_export']  = NM_charset_to_utf8($this->Rtf_f);
          $this->Arr_result['title_export'] = NM_charset_to_utf8($this->Tit_doc);
          $Temp = ob_get_clean();
          if ($Temp !== false && trim($Temp) != "")
          {
              $this->Arr_result['htmOutput'] = NM_charset_to_utf8($Temp);
          }
          $oJson = new Services_JSON();
          echo $oJson->encode($this->Arr_result);
          exit;
      }
      else
      {
          $this->progress_bar_end();
      }
   }

   //----- 
   function inicializa_vars()
   {
      global $nm_lang;
      if (isset($GLOBALS['nmgp_parms']) && !empty($GLOBALS['nmgp_parms'])) 
      { 
          $GLOBALS['nmgp_parms'] = str_replace("@aspass@", "'", $GLOBALS['nmgp_parms']);
          $todox = str_replace("?#?@?@?", "?#?@ ?@?", $GLOBALS["nmgp_parms"]);
          $todo  = explode("?@?", $todox);
          foreach ($todo as $param)
          {
               $cadapar = explode("?#?", $param);
               if (1 < sizeof($cadapar))
               {
                   if (substr($cadapar[0], 0, 11) == "SC_glo_par_")
                   {
                       $cadapar[0] = substr($cadapar[0], 11);
                       $cadapar[1] = $_SESSION[$cadapar[1]];
                   }
                   if (isset($GLOBALS['sc_conv_var'][$cadapar[0]]))
                   {
                       $cadapar[0] = $GLOBALS['sc_conv_var'][$cadapar[0]];
                   }
                   elseif (isset($GLOBALS['sc_conv_var'][strtolower($cadapar[0])]))
                   {
                       $cadapar[0] = $GLOBALS['sc_conv_var'][strtolower($cadapar[0])];
                   }
                   nm_limpa_str_cons_jogos_brasil($cadapar[1]);
                   nm_protect_num_cons_jogos_brasil($cadapar[0], $cadapar[1]);
                   if ($cadapar[1] == "@ ") {$cadapar[1] = trim($cadapar[1]); }
                   $Tmp_par   = $cadapar[0];
                   $$Tmp_par = $cadapar[1];
                   if ($Tmp_par == "nmgp_opcao")
                   {
                       $_SESSION['sc_session'][$script_case_init]['cons_jogos_brasil']['opcao'] = $cadapar[1];
                   }
               }
          }
      }
      if (isset($time_casa)) 
      {
          $_SESSION['time_casa'] = $time_casa;
          nm_limpa_str_cons_jogos_brasil($_SESSION["time_casa"]);
      }
      if (isset($time_visitante)) 
      {
          $_SESSION['time_visitante'] = $time_visitante;
          nm_limpa_str_cons_jogos_brasil($_SESSION["time_visitante"]);
      }
      $dir_raiz          = strrpos($_SERVER['PHP_SELF'],"/") ;  
      $dir_raiz          = substr($_SERVER['PHP_SELF'], 0, $dir_raiz + 1) ;  
      $this->nm_location = $this->Ini->sc_protocolo . $this->Ini->server . $dir_raiz; 
      require_once($this->Ini->path_aplicacao . "cons_jogos_brasil_total.class.php"); 
      $this->Tot      = new cons_jogos_brasil_total($this->Ini->sc_page);
      $this->prep_modulos("Tot");
      $Gb_geral = "quebra_geral_" . $_SESSION['sc_session'][$this->Ini->sc_page]['cons_jogos_brasil']['SC_Ind_Groupby'];
      if (method_exists($this->Tot,$Gb_geral))
      {
          $this->Tot->$Gb_geral();
          $this->count_ger = $_SESSION['sc_session'][$this->Ini->sc_page]['cons_jogos_brasil']['tot_geral'][1];
      }
      if (!$this->Ini->sc_export_ajax) {
          require_once($this->Ini->path_lib_php . "/sc_progress_bar.php");
          $this->pb = new scProgressBar();
          $this->pb->setRoot($this->Ini->root);
          $this->pb->setDir($_SESSION['scriptcase']['cons_jogos_brasil']['glo_nm_path_imag_temp'] . "/");
          $this->pb->setProgressbarMd5($_GET['pbmd5']);
          $this->pb->initialize();
          $this->pb->setReturnUrl("./");
          $this->pb->setReturnOption('volta_grid');
          $this->pb->setTotalSteps($this->count_ger);
      }
      $this->Arquivo    = "sc_rtf";
      $this->Arquivo   .= "_" . date("YmdHis") . "_" . rand(0, 1000);
      $this->Arquivo   .= "_cons_jogos_brasil";
      $this->Arquivo   .= ".rtf";
      $this->Tit_doc    = "cons_jogos_brasil.rtf";
   }
   //---- 
   function prep_modulos($modulo)
   {
      $this->$modulo->Ini    = $this->Ini;
      $this->$modulo->Db     = $this->Db;
      $this->$modulo->Erro   = $this->Erro;
      $this->$modulo->Lookup = $this->Lookup;
   }


   //----- 
   function gera_texto_tag()
   {
     global $nm_lang;
      global $nm_nada, $nm_lang;

      $_SESSION['scriptcase']['sc_sql_ult_conexao'] = ''; 
      $this->sc_proc_grid = false; 
      $nm_raiz_img  = ""; 
      if (isset($_SESSION['scriptcase']['sc_apl_conf']['cons_jogos_brasil']['field_display']) && !empty($_SESSION['scriptcase']['sc_apl_conf']['cons_jogos_brasil']['field_display']))
      {
          foreach ($_SESSION['scriptcase']['sc_apl_conf']['cons_jogos_brasil']['field_display'] as $NM_cada_field => $NM_cada_opc)
          {
              $this->NM_cmp_hidden[$NM_cada_field] = $NM_cada_opc;
          }
      }
      if (isset($_SESSION['sc_session'][$this->Ini->sc_page]['cons_jogos_brasil']['usr_cmp_sel']) && !empty($_SESSION['sc_session'][$this->Ini->sc_page]['cons_jogos_brasil']['usr_cmp_sel']))
      {
          foreach ($_SESSION['sc_session'][$this->Ini->sc_page]['cons_jogos_brasil']['usr_cmp_sel'] as $NM_cada_field => $NM_cada_opc)
          {
              $this->NM_cmp_hidden[$NM_cada_field] = $NM_cada_opc;
          }
      }
      if (isset($_SESSION['sc_session'][$this->Ini->sc_page]['cons_jogos_brasil']['php_cmp_sel']) && !empty($_SESSION['sc_session'][$this->Ini->sc_page]['cons_jogos_brasil']['php_cmp_sel']))
      {
          foreach ($_SESSION['sc_session'][$this->Ini->sc_page]['cons_jogos_brasil']['php_cmp_sel'] as $NM_cada_field => $NM_cada_opc)
          {
              $this->NM_cmp_hidden[$NM_cada_field] = $NM_cada_opc;
          }
      }
      $this->sc_where_orig   = $_SESSION['sc_session'][$this->Ini->sc_page]['cons_jogos_brasil']['where_orig'];
      $this->sc_where_atual  = $_SESSION['sc_session'][$this->Ini->sc_page]['cons_jogos_brasil']['where_pesq'];
      $this->sc_where_filtro = $_SESSION['sc_session'][$this->Ini->sc_page]['cons_jogos_brasil']['where_pesq_filtro'];
      if (isset($_SESSION['sc_session'][$this->Ini->sc_page]['cons_jogos_brasil']['campos_busca']) && !empty($_SESSION['sc_session'][$this->Ini->sc_page]['cons_jogos_brasil']['campos_busca']))
      { 
          $Busca_temp = $_SESSION['sc_session'][$this->Ini->sc_page]['cons_jogos_brasil']['campos_busca'];
          if ($_SESSION['scriptcase']['charset'] != "UTF-8")
          {
              $Busca_temp = NM_conv_charset($Busca_temp, $_SESSION['scriptcase']['charset'], "UTF-8");
          }
          $this->jogo_id = $Busca_temp['jogo_id']; 
          $tmp_pos = strpos($this->jogo_id, "##@@");
          if ($tmp_pos !== false && !is_array($this->jogo_id))
          {
              $this->jogo_id = substr($this->jogo_id, 0, $tmp_pos);
          }
          $this->data_jogo = $Busca_temp['data_jogo']; 
          $tmp_pos = strpos($this->data_jogo, "##@@");
          if ($tmp_pos !== false && !is_array($this->data_jogo))
          {
              $this->data_jogo = substr($this->data_jogo, 0, $tmp_pos);
          }
          $this->data_jogo_2 = $Busca_temp['data_jogo_input_2']; 
          $this->time_casa_id = $Busca_temp['time_casa_id']; 
          $tmp_pos = strpos($this->time_casa_id, "##@@");
          if ($tmp_pos !== false && !is_array($this->time_casa_id))
          {
              $this->time_casa_id = substr($this->time_casa_id, 0, $tmp_pos);
          }
          $this->time_visitante_id = $Busca_temp['time_visitante_id']; 
          $tmp_pos = strpos($this->time_visitante_id, "##@@");
          if ($tmp_pos !== false && !is_array($this->time_visitante_id))
          {
              $this->time_visitante_id = substr($this->time_visitante_id, 0, $tmp_pos);
          }
      } 
      if (isset($_SESSION['sc_session'][$this->Ini->sc_page]['cons_jogos_brasil']['rtf_name']))
      {
          $Pos = strrpos($_SESSION['sc_session'][$this->Ini->sc_page]['cons_jogos_brasil']['rtf_name'], ".");
          if ($Pos === false) {
              $_SESSION['sc_session'][$this->Ini->sc_page]['cons_jogos_brasil']['rtf_name'] .= ".rtf";
          }
          $this->Arquivo = $_SESSION['sc_session'][$this->Ini->sc_page]['cons_jogos_brasil']['rtf_name'];
          $this->Tit_doc = $_SESSION['sc_session'][$this->Ini->sc_page]['cons_jogos_brasil']['rtf_name'];
          unset($_SESSION['sc_session'][$this->Ini->sc_page]['cons_jogos_brasil']['rtf_name']);
      }
      $this->arr_export = array('label' => array(), 'lines' => array());
      $this->arr_span   = array();

      $this->Texto_tag .= "<table>\r\n";
      $this->Texto_tag .= "<tr>\r\n";
      foreach ($_SESSION['sc_session'][$this->Ini->sc_page]['cons_jogos_brasil']['field_order'] as $Cada_col)
      { 
          $SC_Label = (isset($this->New_label['data_jogo'])) ? $this->New_label['data_jogo'] : "Data Jogo"; 
          if ($Cada_col == "data_jogo" && (!isset($this->NM_cmp_hidden[$Cada_col]) || $this->NM_cmp_hidden[$Cada_col] != "off"))
          {
              $SC_Label = NM_charset_to_utf8($SC_Label);
              $SC_Label = str_replace('<', '&lt;', $SC_Label);
              $SC_Label = str_replace('>', '&gt;', $SC_Label);
              $this->Texto_tag .= "<td>" . $SC_Label . "</td>\r\n";
          }
          $SC_Label = (isset($this->New_label['horario'])) ? $this->New_label['horario'] : "Horario"; 
          if ($Cada_col == "horario" && (!isset($this->NM_cmp_hidden[$Cada_col]) || $this->NM_cmp_hidden[$Cada_col] != "off"))
          {
              $SC_Label = NM_charset_to_utf8($SC_Label);
              $SC_Label = str_replace('<', '&lt;', $SC_Label);
              $SC_Label = str_replace('>', '&gt;', $SC_Label);
              $this->Texto_tag .= "<td>" . $SC_Label . "</td>\r\n";
          }
          $SC_Label = (isset($this->New_label['time_casa_id'])) ? $this->New_label['time_casa_id'] : "Sele????o"; 
          if ($Cada_col == "time_casa_id" && (!isset($this->NM_cmp_hidden[$Cada_col]) || $this->NM_cmp_hidden[$Cada_col] != "off"))
          {
              $SC_Label = NM_charset_to_utf8($SC_Label);
              $SC_Label = str_replace('<', '&lt;', $SC_Label);
              $SC_Label = str_replace('>', '&gt;', $SC_Label);
              $this->Texto_tag .= "<td>" . $SC_Label . "</td>\r\n";
          }
          $SC_Label = (isset($this->New_label['x'])) ? $this->New_label['x'] : ""; 
          if ($Cada_col == "x" && (!isset($this->NM_cmp_hidden[$Cada_col]) || $this->NM_cmp_hidden[$Cada_col] != "off"))
          {
              $SC_Label = NM_charset_to_utf8($SC_Label);
              $SC_Label = str_replace('<', '&lt;', $SC_Label);
              $SC_Label = str_replace('>', '&gt;', $SC_Label);
              $this->Texto_tag .= "<td>" . $SC_Label . "</td>\r\n";
          }
          $SC_Label = (isset($this->New_label['time_visitante_id'])) ? $this->New_label['time_visitante_id'] : "Sele????o"; 
          if ($Cada_col == "time_visitante_id" && (!isset($this->NM_cmp_hidden[$Cada_col]) || $this->NM_cmp_hidden[$Cada_col] != "off"))
          {
              $SC_Label = NM_charset_to_utf8($SC_Label);
              $SC_Label = str_replace('<', '&lt;', $SC_Label);
              $SC_Label = str_replace('>', '&gt;', $SC_Label);
              $this->Texto_tag .= "<td>" . $SC_Label . "</td>\r\n";
          }
          $SC_Label = (isset($this->New_label['rodada'])) ? $this->New_label['rodada'] : "Rodada"; 
          if ($Cada_col == "rodada" && (!isset($this->NM_cmp_hidden[$Cada_col]) || $this->NM_cmp_hidden[$Cada_col] != "off"))
          {
              $SC_Label = NM_charset_to_utf8($SC_Label);
              $SC_Label = str_replace('<', '&lt;', $SC_Label);
              $SC_Label = str_replace('>', '&gt;', $SC_Label);
              $this->Texto_tag .= "<td>" . $SC_Label . "</td>\r\n";
          }
          $SC_Label = (isset($this->New_label['competicao_id'])) ? $this->New_label['competicao_id'] : "Competi????o"; 
          if ($Cada_col == "competicao_id" && (!isset($this->NM_cmp_hidden[$Cada_col]) || $this->NM_cmp_hidden[$Cada_col] != "off"))
          {
              $SC_Label = NM_charset_to_utf8($SC_Label);
              $SC_Label = str_replace('<', '&lt;', $SC_Label);
              $SC_Label = str_replace('>', '&gt;', $SC_Label);
              $this->Texto_tag .= "<td>" . $SC_Label . "</td>\r\n";
          }
          $SC_Label = (isset($this->New_label['jogo_id'])) ? $this->New_label['jogo_id'] : "Jogo Id"; 
          if ($Cada_col == "jogo_id" && (!isset($this->NM_cmp_hidden[$Cada_col]) || $this->NM_cmp_hidden[$Cada_col] != "off"))
          {
              $SC_Label = NM_charset_to_utf8($SC_Label);
              $SC_Label = str_replace('<', '&lt;', $SC_Label);
              $SC_Label = str_replace('>', '&gt;', $SC_Label);
              $this->Texto_tag .= "<td>" . $SC_Label . "</td>\r\n";
          }
          $SC_Label = (isset($this->New_label['time_visitante_placar'])) ? $this->New_label['time_visitante_placar'] : "Time Visitante Placar"; 
          if ($Cada_col == "time_visitante_placar" && (!isset($this->NM_cmp_hidden[$Cada_col]) || $this->NM_cmp_hidden[$Cada_col] != "off"))
          {
              $SC_Label = NM_charset_to_utf8($SC_Label);
              $SC_Label = str_replace('<', '&lt;', $SC_Label);
              $SC_Label = str_replace('>', '&gt;', $SC_Label);
              $this->Texto_tag .= "<td>" . $SC_Label . "</td>\r\n";
          }
          $SC_Label = (isset($this->New_label['time_casa_placar'])) ? $this->New_label['time_casa_placar'] : "Time Casa Placar"; 
          if ($Cada_col == "time_casa_placar" && (!isset($this->NM_cmp_hidden[$Cada_col]) || $this->NM_cmp_hidden[$Cada_col] != "off"))
          {
              $SC_Label = NM_charset_to_utf8($SC_Label);
              $SC_Label = str_replace('<', '&lt;', $SC_Label);
              $SC_Label = str_replace('>', '&gt;', $SC_Label);
              $this->Texto_tag .= "<td>" . $SC_Label . "</td>\r\n";
          }
      } 
      $this->Texto_tag .= "</tr>\r\n";
      $this->nm_field_dinamico = array();
      $this->nm_order_dinamico = array();
      $nmgp_select_count = "SELECT count(*) AS countTest from " . $this->Ini->nm_tabela; 
      if (in_array(strtolower($this->Ini->nm_tpbanco), $this->Ini->nm_bases_sybase))
      { 
          $nmgp_select = "SELECT str_replace (convert(char(10),data_jogo,102), '.', '-') + ' ' + convert(char(8),data_jogo,20), str_replace (convert(char(10),horario,102), '.', '-') + ' ' + convert(char(8),horario,20), time_casa_id, time_visitante_id, rodada, competicao_id, jogo_id, time_visitante_placar, time_casa_placar, jogo_api_id from " . $this->Ini->nm_tabela; 
      } 
      elseif (in_array(strtolower($this->Ini->nm_tpbanco), $this->Ini->nm_bases_mysql))
      { 
          $nmgp_select = "SELECT data_jogo, horario, time_casa_id, time_visitante_id, rodada, competicao_id, jogo_id, time_visitante_placar, time_casa_placar, jogo_api_id from " . $this->Ini->nm_tabela; 
      } 
      elseif (in_array(strtolower($this->Ini->nm_tpbanco), $this->Ini->nm_bases_mssql))
      { 
       $nmgp_select = "SELECT convert(char(23),data_jogo,121), convert(char(23),horario,121), time_casa_id, time_visitante_id, rodada, competicao_id, jogo_id, time_visitante_placar, time_casa_placar, jogo_api_id from " . $this->Ini->nm_tabela; 
      } 
      elseif (in_array(strtolower($this->Ini->nm_tpbanco), $this->Ini->nm_bases_oracle))
      { 
          $nmgp_select = "SELECT data_jogo, horario, time_casa_id, time_visitante_id, rodada, competicao_id, jogo_id, time_visitante_placar, time_casa_placar, jogo_api_id from " . $this->Ini->nm_tabela; 
      } 
      elseif (in_array(strtolower($this->Ini->nm_tpbanco), $this->Ini->nm_bases_informix))
      { 
          $nmgp_select = "SELECT EXTEND(data_jogo, YEAR TO DAY), horario, time_casa_id, time_visitante_id, rodada, competicao_id, jogo_id, time_visitante_placar, time_casa_placar, jogo_api_id from " . $this->Ini->nm_tabela; 
      } 
      else 
      { 
          $nmgp_select = "SELECT data_jogo, horario, time_casa_id, time_visitante_id, rodada, competicao_id, jogo_id, time_visitante_placar, time_casa_placar, jogo_api_id from " . $this->Ini->nm_tabela; 
      } 
      $nmgp_select .= " " . $_SESSION['sc_session'][$this->Ini->sc_page]['cons_jogos_brasil']['where_pesq'];
      $nmgp_select_count .= " " . $_SESSION['sc_session'][$this->Ini->sc_page]['cons_jogos_brasil']['where_pesq'];
      $nmgp_order_by = $_SESSION['sc_session'][$this->Ini->sc_page]['cons_jogos_brasil']['order_grid'];
      $nmgp_select .= $nmgp_order_by; 
      $_SESSION['scriptcase']['sc_sql_ult_comando'] = $nmgp_select_count;
      $rt = $this->Db->Execute($nmgp_select_count);
      if ($rt === false && !$rt->EOF && $GLOBALS["NM_ERRO_IBASE"] != 1)
      {
         $this->Erro->mensagem(__FILE__, __LINE__, "banco", $this->Ini->Nm_lang['lang_errm_dber'], $this->Db->ErrorMsg());
         exit;
      }
      $this->count_ger = $rt->fields[0];
      $rt->Close();
      $_SESSION['scriptcase']['sc_sql_ult_comando'] = $nmgp_select;
      $rs = $this->Db->Execute($nmgp_select);
      if ($rs === false && !$rs->EOF && $GLOBALS["NM_ERRO_IBASE"] != 1)
      {
         $this->Erro->mensagem(__FILE__, __LINE__, "banco", $this->Ini->Nm_lang['lang_errm_dber'], $this->Db->ErrorMsg());
         exit;
      }
      $this->SC_seq_register = 0;
      $PB_tot = (isset($this->count_ger) && $this->count_ger > 0) ? "/" . $this->count_ger : "";
      while (!$rs->EOF)
      {
         $this->SC_seq_register++;
         if (!$this->Ini->sc_export_ajax) {
             $Mens_bar = NM_charset_to_utf8($this->Ini->Nm_lang['lang_othr_prcs']);
             $this->pb->setProgressbarMessage($Mens_bar . ": " . $this->SC_seq_register . $PB_tot);
             $this->pb->addSteps(1);
         }
         $this->Texto_tag .= "<tr>\r\n";
         $this->data_jogo = $rs->fields[0] ;  
         $this->horario = $rs->fields[1] ;  
         $this->time_casa_id = $rs->fields[2] ;  
         $this->time_casa_id = (string)$this->time_casa_id;
         $this->time_visitante_id = $rs->fields[3] ;  
         $this->time_visitante_id = (string)$this->time_visitante_id;
         $this->rodada = $rs->fields[4] ;  
         $this->rodada = (string)$this->rodada;
         $this->competicao_id = $rs->fields[5] ;  
         $this->competicao_id = (string)$this->competicao_id;
         $this->jogo_id = $rs->fields[6] ;  
         $this->jogo_id = (string)$this->jogo_id;
         $this->time_visitante_placar = $rs->fields[7] ;  
         $this->time_visitante_placar = (string)$this->time_visitante_placar;
         $this->time_casa_placar = $rs->fields[8] ;  
         $this->time_casa_placar = (string)$this->time_casa_placar;
         $this->jogo_api_id = $rs->fields[9] ;  
         $this->jogo_api_id = (string)$this->jogo_api_id;
         $this->Orig_data_jogo = $this->data_jogo;
         $this->Orig_horario = $this->horario;
         $this->Orig_time_casa_id = $this->time_casa_id;
         $this->Orig_time_visitante_id = $this->time_visitante_id;
         $this->Orig_rodada = $this->rodada;
         $this->Orig_competicao_id = $this->competicao_id;
         $this->Orig_jogo_id = $this->jogo_id;
         $this->Orig_time_visitante_placar = $this->time_visitante_placar;
         $this->Orig_time_casa_placar = $this->time_casa_placar;
         $this->Orig_jogo_api_id = $this->jogo_api_id;
         //----- lookup - time_casa_id
         $this->look_time_casa_id = $this->time_casa_id; 
         $this->Lookup->lookup_time_casa_id($this->look_time_casa_id, $this->time_casa_id) ; 
         $this->look_time_casa_id = ($this->look_time_casa_id == "&nbsp;") ? "" : $this->look_time_casa_id; 
         //----- lookup - time_visitante_id
         $this->look_time_visitante_id = $this->time_visitante_id; 
         $this->Lookup->lookup_time_visitante_id($this->look_time_visitante_id, $this->time_visitante_id) ; 
         $this->look_time_visitante_id = ($this->look_time_visitante_id == "&nbsp;") ? "" : $this->look_time_visitante_id; 
         //----- lookup - competicao_id
         $this->look_competicao_id = $this->competicao_id; 
         $this->Lookup->lookup_competicao_id($this->look_competicao_id, $this->competicao_id) ; 
         $this->look_competicao_id = ($this->look_competicao_id == "&nbsp;") ? "" : $this->look_competicao_id; 
         $this->sc_proc_grid = true; 
         $_SESSION['scriptcase']['cons_jogos_brasil']['contr_erro'] = 'on';
if (!isset($_SESSION['time_visitante'])) {$_SESSION['time_visitante'] = "";}
if (!isset($this->sc_temp_time_visitante)) {$this->sc_temp_time_visitante = (isset($_SESSION['time_visitante'])) ? $_SESSION['time_visitante'] : "";}
if (!isset($_SESSION['time_casa'])) {$_SESSION['time_casa'] = "";}
if (!isset($this->sc_temp_time_casa)) {$this->sc_temp_time_casa = (isset($_SESSION['time_casa'])) ? $_SESSION['time_casa'] : "";}
  $this->x  = 'X';
$this->sc_temp_time_casa = $this->getNomeClube($this->time_casa_id );
$this->sc_temp_time_visitante = $this->getNomeClube($this->time_visitante_id );

if (isset($this->sc_temp_time_casa)) {$_SESSION['time_casa'] = $this->sc_temp_time_casa;}
if (isset($this->sc_temp_time_visitante)) {$_SESSION['time_visitante'] = $this->sc_temp_time_visitante;}
$_SESSION['scriptcase']['cons_jogos_brasil']['contr_erro'] = 'off'; 
         foreach ($_SESSION['sc_session'][$this->Ini->sc_page]['cons_jogos_brasil']['field_order'] as $Cada_col)
         { 
            if (!isset($this->NM_cmp_hidden[$Cada_col]) || $this->NM_cmp_hidden[$Cada_col] != "off")
            { 
                $NM_func_exp = "NM_export_" . $Cada_col;
                $this->$NM_func_exp();
            } 
         } 
         $this->Texto_tag .= "</tr>\r\n";
         $rs->MoveNext();
      }
      $this->Texto_tag .= "</table>\r\n";
      if(isset($_SESSION['sc_session'][$this->Ini->sc_page]['cons_jogos_brasil']['export_sel_columns']['field_order']))
      {
          $_SESSION['sc_session'][$this->Ini->sc_page]['cons_jogos_brasil']['field_order'] = $_SESSION['sc_session'][$this->Ini->sc_page]['cons_jogos_brasil']['export_sel_columns']['field_order'];
          unset($_SESSION['sc_session'][$this->Ini->sc_page]['cons_jogos_brasil']['export_sel_columns']['field_order']);
      }
      if(isset($_SESSION['sc_session'][$this->Ini->sc_page]['cons_jogos_brasil']['export_sel_columns']['usr_cmp_sel']))
      {
          $_SESSION['sc_session'][$this->Ini->sc_page]['cons_jogos_brasil']['usr_cmp_sel'] = $_SESSION['sc_session'][$this->Ini->sc_page]['cons_jogos_brasil']['export_sel_columns']['usr_cmp_sel'];
          unset($_SESSION['sc_session'][$this->Ini->sc_page]['cons_jogos_brasil']['export_sel_columns']['usr_cmp_sel']);
      }
      $rs->Close();
   }
   //----- data_jogo
   function NM_export_data_jogo()
   {
             $conteudo_x =  $this->data_jogo;
             nm_conv_limpa_dado($conteudo_x, "YYYY-MM-DD");
             if (is_numeric($conteudo_x) && strlen($conteudo_x) > 0) 
             { 
                 $this->nm_data->SetaData($this->data_jogo, "YYYY-MM-DD  ");
                 $this->data_jogo = $this->nm_data->FormataSaida($this->nm_data->FormatRegion("DT", "ddmmaaaa"));
             } 
         $this->data_jogo = NM_charset_to_utf8($this->data_jogo);
         $this->data_jogo = str_replace('<', '&lt;', $this->data_jogo);
         $this->data_jogo = str_replace('>', '&gt;', $this->data_jogo);
         $this->Texto_tag .= "<td>" . $this->data_jogo . "</td>\r\n";
   }
   //----- horario
   function NM_export_horario()
   {
             $conteudo_x =  $this->horario;
             nm_conv_limpa_dado($conteudo_x, "HH:II:SS");
             if (is_numeric($conteudo_x) && strlen($conteudo_x) > 0) 
             { 
                 $this->nm_data->SetaData($this->horario, "HH:II:SS  ");
                 $this->horario = $this->nm_data->FormataSaida($this->nm_data->FormatRegion("HH", "hhiiss"));
             } 
         $this->horario = NM_charset_to_utf8($this->horario);
         $this->horario = str_replace('<', '&lt;', $this->horario);
         $this->horario = str_replace('>', '&gt;', $this->horario);
         $this->Texto_tag .= "<td>" . $this->horario . "</td>\r\n";
   }
   //----- time_casa_id
   function NM_export_time_casa_id()
   {
         nmgp_Form_Num_Val($this->look_time_casa_id, $_SESSION['scriptcase']['reg_conf']['grup_num'], $_SESSION['scriptcase']['reg_conf']['dec_num'], "0", "S", "2", "", "N:" . $_SESSION['scriptcase']['reg_conf']['neg_num'] , $_SESSION['scriptcase']['reg_conf']['simb_neg'], $_SESSION['scriptcase']['reg_conf']['num_group_digit']) ; 
         $this->look_time_casa_id = NM_charset_to_utf8($this->look_time_casa_id);
         $this->look_time_casa_id = str_replace('<', '&lt;', $this->look_time_casa_id);
         $this->look_time_casa_id = str_replace('>', '&gt;', $this->look_time_casa_id);
         $this->Texto_tag .= "<td>" . $this->look_time_casa_id . "</td>\r\n";
   }
   //----- x
   function NM_export_x()
   {
         $this->x = html_entity_decode($this->x, ENT_COMPAT, $_SESSION['scriptcase']['charset']);
         $this->x = strip_tags($this->x);
         $this->x = NM_charset_to_utf8($this->x);
         $this->x = str_replace('<', '&lt;', $this->x);
         $this->x = str_replace('>', '&gt;', $this->x);
         $this->Texto_tag .= "<td>" . $this->x . "</td>\r\n";
   }
   //----- time_visitante_id
   function NM_export_time_visitante_id()
   {
         nmgp_Form_Num_Val($this->look_time_visitante_id, $_SESSION['scriptcase']['reg_conf']['grup_num'], $_SESSION['scriptcase']['reg_conf']['dec_num'], "0", "S", "2", "", "N:" . $_SESSION['scriptcase']['reg_conf']['neg_num'] , $_SESSION['scriptcase']['reg_conf']['simb_neg'], $_SESSION['scriptcase']['reg_conf']['num_group_digit']) ; 
         $this->look_time_visitante_id = NM_charset_to_utf8($this->look_time_visitante_id);
         $this->look_time_visitante_id = str_replace('<', '&lt;', $this->look_time_visitante_id);
         $this->look_time_visitante_id = str_replace('>', '&gt;', $this->look_time_visitante_id);
         $this->Texto_tag .= "<td>" . $this->look_time_visitante_id . "</td>\r\n";
   }
   //----- rodada
   function NM_export_rodada()
   {
             nmgp_Form_Num_Val($this->rodada, $_SESSION['scriptcase']['reg_conf']['grup_num'], $_SESSION['scriptcase']['reg_conf']['dec_num'], "0", "S", "2", "", "N:" . $_SESSION['scriptcase']['reg_conf']['neg_num'] , $_SESSION['scriptcase']['reg_conf']['simb_neg'], $_SESSION['scriptcase']['reg_conf']['num_group_digit']) ; 
         $this->rodada = NM_charset_to_utf8($this->rodada);
         $this->rodada = str_replace('<', '&lt;', $this->rodada);
         $this->rodada = str_replace('>', '&gt;', $this->rodada);
         $this->Texto_tag .= "<td>" . $this->rodada . "</td>\r\n";
   }
   //----- competicao_id
   function NM_export_competicao_id()
   {
         nmgp_Form_Num_Val($this->look_competicao_id, $_SESSION['scriptcase']['reg_conf']['grup_num'], $_SESSION['scriptcase']['reg_conf']['dec_num'], "0", "S", "2", "", "N:" . $_SESSION['scriptcase']['reg_conf']['neg_num'] , $_SESSION['scriptcase']['reg_conf']['simb_neg'], $_SESSION['scriptcase']['reg_conf']['num_group_digit']) ; 
         $this->look_competicao_id = NM_charset_to_utf8($this->look_competicao_id);
         $this->look_competicao_id = str_replace('<', '&lt;', $this->look_competicao_id);
         $this->look_competicao_id = str_replace('>', '&gt;', $this->look_competicao_id);
         $this->Texto_tag .= "<td>" . $this->look_competicao_id . "</td>\r\n";
   }
   //----- jogo_id
   function NM_export_jogo_id()
   {
             nmgp_Form_Num_Val($this->jogo_id, $_SESSION['scriptcase']['reg_conf']['grup_num'], $_SESSION['scriptcase']['reg_conf']['dec_num'], "0", "S", "2", "", "N:" . $_SESSION['scriptcase']['reg_conf']['neg_num'] , $_SESSION['scriptcase']['reg_conf']['simb_neg'], $_SESSION['scriptcase']['reg_conf']['num_group_digit']) ; 
         $this->jogo_id = NM_charset_to_utf8($this->jogo_id);
         $this->jogo_id = str_replace('<', '&lt;', $this->jogo_id);
         $this->jogo_id = str_replace('>', '&gt;', $this->jogo_id);
         $this->Texto_tag .= "<td>" . $this->jogo_id . "</td>\r\n";
   }
   //----- time_visitante_placar
   function NM_export_time_visitante_placar()
   {
             nmgp_Form_Num_Val($this->time_visitante_placar, $_SESSION['scriptcase']['reg_conf']['grup_num'], $_SESSION['scriptcase']['reg_conf']['dec_num'], "0", "S", "2", "", "N:" . $_SESSION['scriptcase']['reg_conf']['neg_num'] , $_SESSION['scriptcase']['reg_conf']['simb_neg'], $_SESSION['scriptcase']['reg_conf']['num_group_digit']) ; 
         $this->time_visitante_placar = NM_charset_to_utf8($this->time_visitante_placar);
         $this->time_visitante_placar = str_replace('<', '&lt;', $this->time_visitante_placar);
         $this->time_visitante_placar = str_replace('>', '&gt;', $this->time_visitante_placar);
         $this->Texto_tag .= "<td>" . $this->time_visitante_placar . "</td>\r\n";
   }
   //----- time_casa_placar
   function NM_export_time_casa_placar()
   {
             nmgp_Form_Num_Val($this->time_casa_placar, $_SESSION['scriptcase']['reg_conf']['grup_num'], $_SESSION['scriptcase']['reg_conf']['dec_num'], "0", "S", "2", "", "N:" . $_SESSION['scriptcase']['reg_conf']['neg_num'] , $_SESSION['scriptcase']['reg_conf']['simb_neg'], $_SESSION['scriptcase']['reg_conf']['num_group_digit']) ; 
         $this->time_casa_placar = NM_charset_to_utf8($this->time_casa_placar);
         $this->time_casa_placar = str_replace('<', '&lt;', $this->time_casa_placar);
         $this->time_casa_placar = str_replace('>', '&gt;', $this->time_casa_placar);
         $this->Texto_tag .= "<td>" . $this->time_casa_placar . "</td>\r\n";
   }

   //----- 
   function grava_arquivo_rtf()
   {
      global $nm_lang, $doc_wrap;
      $this->Rtf_f = $this->Ini->root . $this->Ini->path_imag_temp . "/" . $this->Arquivo;
      $rtf_f       = fopen($this->Ini->root . $this->Ini->path_imag_temp . "/" . $this->Arquivo, "w");
      require_once($this->Ini->path_third      . "/rtf_new/document_generator/cl_xml2driver.php"); 
      $text_ok  =  "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\r\n"; 
      $text_ok .=  "<DOC config_file=\"" . $this->Ini->path_third . "/rtf_new/doc_config.inc\" >\r\n"; 
      $text_ok .=  $this->Texto_tag; 
      $text_ok .=  "</DOC>\r\n"; 
      $xml = new nDOCGEN($text_ok,"RTF"); 
      fwrite($rtf_f, $xml->get_result_file());
      fclose($rtf_f);
   }

   function nm_conv_data_db($dt_in, $form_in, $form_out)
   {
       $dt_out = $dt_in;
       if (strtoupper($form_in) == "DB_FORMAT") {
           if ($dt_out == "null" || $dt_out == "")
           {
               $dt_out = "";
               return $dt_out;
           }
           $form_in = "AAAA-MM-DD";
       }
       if (strtoupper($form_out) == "DB_FORMAT") {
           if (empty($dt_out))
           {
               $dt_out = "null";
               return $dt_out;
           }
           $form_out = "AAAA-MM-DD";
       }
       if (strtoupper($form_out) == "SC_FORMAT_REGION") {
           $this->nm_data->SetaData($dt_in, strtoupper($form_in));
           $prep_out  = (strpos(strtolower($form_in), "dd") !== false) ? "dd" : "";
           $prep_out .= (strpos(strtolower($form_in), "mm") !== false) ? "mm" : "";
           $prep_out .= (strpos(strtolower($form_in), "aa") !== false) ? "aaaa" : "";
           $prep_out .= (strpos(strtolower($form_in), "yy") !== false) ? "aaaa" : "";
           return $this->nm_data->FormataSaida($this->nm_data->FormatRegion("DT", $prep_out));
       }
       else {
           nm_conv_form_data($dt_out, $form_in, $form_out);
           return $dt_out;
       }
   }
   function progress_bar_end()
   {
      unset($_SESSION['sc_session'][$this->Ini->sc_page]['cons_jogos_brasil']['rtf_file']);
      if (is_file($this->Ini->root . $this->Ini->path_imag_temp . "/" . $this->Arquivo))
      {
          $_SESSION['sc_session'][$this->Ini->sc_page]['cons_jogos_brasil']['rtf_file'] = $this->Ini->root . $this->Ini->path_imag_temp . "/" . $this->Arquivo;
      }
      $path_doc_md5 = md5($this->Ini->path_imag_temp . "/" . $this->Arquivo);
      $_SESSION['sc_session'][$this->Ini->sc_page]['cons_jogos_brasil'][$path_doc_md5][0] = $this->Ini->path_imag_temp . "/" . $this->Arquivo;
      $_SESSION['sc_session'][$this->Ini->sc_page]['cons_jogos_brasil'][$path_doc_md5][1] = $this->Tit_doc;
      $Mens_bar = $this->Ini->Nm_lang['lang_othr_file_msge'];
      if ($_SESSION['scriptcase']['charset'] != "UTF-8") {
          $Mens_bar = sc_convert_encoding($Mens_bar, "UTF-8", $_SESSION['scriptcase']['charset']);
      }
      $this->pb->setProgressbarMessage($Mens_bar);
      $this->pb->setDownloadLink($this->Ini->path_imag_temp . "/" . $this->Arquivo);
      $this->pb->setDownloadMd5($path_doc_md5);
      $this->pb->completed();
   }
   //---- 
   function monta_html()
   {
      global $nm_url_saida, $nm_lang;
      include($this->Ini->path_btn . $this->Ini->Str_btn_grid);
      unset($_SESSION['sc_session'][$this->Ini->sc_page]['cons_jogos_brasil']['rtf_file']);
      if (is_file($this->Ini->root . $this->Ini->path_imag_temp . "/" . $this->Arquivo))
      {
          $_SESSION['sc_session'][$this->Ini->sc_page]['cons_jogos_brasil']['rtf_file'] = $this->Ini->root . $this->Ini->path_imag_temp . "/" . $this->Arquivo;
      }
      $path_doc_md5 = md5($this->Ini->path_imag_temp . "/" . $this->Arquivo);
      $_SESSION['sc_session'][$this->Ini->sc_page]['cons_jogos_brasil'][$path_doc_md5][0] = $this->Ini->path_imag_temp . "/" . $this->Arquivo;
      $_SESSION['sc_session'][$this->Ini->sc_page]['cons_jogos_brasil'][$path_doc_md5][1] = $this->Tit_doc;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
            "http://www.w3.org/TR/1999/REC-html401-19991224/loose.dtd">
<HTML<?php echo $_SESSION['scriptcase']['reg_conf']['html_dir'] ?>>
<HEAD>
 <TITLE>Jogos do Brasil :: RTF</TITLE>
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
  <link rel="stylesheet" type="text/css" href="../_lib/css/<?php echo $this->Ini->str_schema_all ?>_export.css" /> 
  <link rel="stylesheet" type="text/css" href="../_lib/css/<?php echo $this->Ini->str_schema_all ?>_export<?php echo $_SESSION['scriptcase']['reg_conf']['css_dir'] ?>.css" /> 
 <?php
 if(isset($this->Ini->str_google_fonts) && !empty($this->Ini->str_google_fonts))
 {
 ?>
    <link rel="stylesheet" type="text/css" href="<?php echo $this->Ini->str_google_fonts ?>" />
 <?php
 }
 ?>
  <link rel="stylesheet" type="text/css" href="../_lib/buttons/<?php echo $this->Ini->Str_btn_css ?>" /> 
</HEAD>
<BODY class="scExportPage">
<?php echo $this->Ini->Ajax_result_set ?>
<table style="border-collapse: collapse; border-width: 0; height: 100%; width: 100%"><tr><td style="padding: 0; text-align: center; vertical-align: middle">
 <table class="scExportTable" align="center">
  <tr>
   <td class="scExportTitle" style="height: 25px">RTF</td>
  </tr>
  <tr>
   <td class="scExportLine" style="width: 100%">
    <table style="border-collapse: collapse; border-width: 0; width: 100%"><tr><td class="scExportLineFont" style="padding: 3px 0 0 0" id="idMessage">
    <?php echo $this->Ini->Nm_lang['lang_othr_file_msge'] ?>
    </td><td class="scExportLineFont" style="text-align:right; padding: 3px 0 0 0">
     <?php echo nmButtonOutput($this->arr_buttons, "bexportview", "document.Fview.submit()", "document.Fview.submit()", "idBtnView", "", "", "", "", "", "", $this->Ini->path_botoes, "", "", "", "", "", "only_text", "text_right", "", "", "", "", "", "", "");
 ?>
     <?php echo nmButtonOutput($this->arr_buttons, "bdownload", "document.Fdown.submit()", "document.Fdown.submit()", "idBtnDown", "", "", "", "", "", "", $this->Ini->path_botoes, "", "", "", "", "", "only_text", "text_right", "", "", "", "", "", "", "");
 ?>
     <?php echo nmButtonOutput($this->arr_buttons, "bvoltar", "document.F0.submit()", "document.F0.submit()", "idBtnBack", "", "", "", "", "", "", $this->Ini->path_botoes, "", "", "", "", "", "only_text", "text_right", "", "", "", "", "", "", "");
 ?>
    </td></tr></table>
   </td>
  </tr>
 </table>
</td></tr></table>
<form name="Fview" method="get" action="<?php echo $this->Ini->path_imag_temp . "/" . $this->Arquivo ?>" target="_blank" style="display: none"> 
</form>
<form name="Fdown" method="get" action="cons_jogos_brasil_download.php" target="_blank" style="display: none"> 
<input type="hidden" name="script_case_init" value="<?php echo NM_encode_input($this->Ini->sc_page); ?>"> 
<input type="hidden" name="nm_tit_doc" value="cons_jogos_brasil"> 
<input type="hidden" name="nm_name_doc" value="<?php echo $path_doc_md5 ?>"> 
</form>
<FORM name="F0" method=post action="./"> 
<INPUT type="hidden" name="script_case_init" value="<?php echo NM_encode_input($this->Ini->sc_page); ?>"> 
<INPUT type="hidden" name="nmgp_opcao" value="volta_grid"> 
</FORM> 
</BODY>
</HTML>
<?php
   }
   function nm_gera_mask(&$nm_campo, $nm_mask)
   { 
      $trab_campo = $nm_campo;
      $trab_mask  = $nm_mask;
      $tam_campo  = strlen($nm_campo);
      $trab_saida = "";
      $str_highlight_ini = "";
      $str_highlight_fim = "";
      if(substr($nm_campo, 0, 23) == '<div class="highlight">' && substr($nm_campo, -6) == '</div>')
      {
           $str_highlight_ini = substr($nm_campo, 0, 23);
           $str_highlight_fim = substr($nm_campo, -6);

           $trab_campo = substr($nm_campo, 23, -6);
           $tam_campo  = strlen($trab_campo);
      }      $mask_num = false;
      for ($x=0; $x < strlen($trab_mask); $x++)
      {
          if (substr($trab_mask, $x, 1) == "#")
          {
              $mask_num = true;
              break;
          }
      }
      if ($mask_num )
      {
          $ver_duas = explode(";", $trab_mask);
          if (isset($ver_duas[1]) && !empty($ver_duas[1]))
          {
              $cont1 = count(explode("#", $ver_duas[0])) - 1;
              $cont2 = count(explode("#", $ver_duas[1])) - 1;
              if ($tam_campo >= $cont2)
              {
                  $trab_mask = $ver_duas[1];
              }
              else
              {
                  $trab_mask = $ver_duas[0];
              }
          }
          $tam_mask = strlen($trab_mask);
          $xdados = 0;
          for ($x=0; $x < $tam_mask; $x++)
          {
              if (substr($trab_mask, $x, 1) == "#" && $xdados < $tam_campo)
              {
                  $trab_saida .= substr($trab_campo, $xdados, 1);
                  $xdados++;
              }
              elseif ($xdados < $tam_campo)
              {
                  $trab_saida .= substr($trab_mask, $x, 1);
              }
          }
          if ($xdados < $tam_campo)
          {
              $trab_saida .= substr($trab_campo, $xdados);
          }
          $nm_campo = $str_highlight_ini . $trab_saida . $str_highlight_ini;
          return;
      }
      for ($ix = strlen($trab_mask); $ix > 0; $ix--)
      {
           $char_mask = substr($trab_mask, $ix - 1, 1);
           if ($char_mask != "x" && $char_mask != "z")
           {
               $trab_saida = $char_mask . $trab_saida;
           }
           else
           {
               if ($tam_campo != 0)
               {
                   $trab_saida = substr($trab_campo, $tam_campo - 1, 1) . $trab_saida;
                   $tam_campo--;
               }
               else
               {
                   $trab_saida = "0" . $trab_saida;
               }
           }
      }
      if ($tam_campo != 0)
      {
          $trab_saida = substr($trab_campo, 0, $tam_campo) . $trab_saida;
          $trab_mask  = str_repeat("z", $tam_campo) . $trab_mask;
      }
   
      $iz = 0; 
      for ($ix = 0; $ix < strlen($trab_mask); $ix++)
      {
           $char_mask = substr($trab_mask, $ix, 1);
           if ($char_mask != "x" && $char_mask != "z")
           {
               if ($char_mask == "." || $char_mask == ",")
               {
                   $trab_saida = substr($trab_saida, 0, $iz) . substr($trab_saida, $iz + 1);
               }
               else
               {
                   $iz++;
               }
           }
           elseif ($char_mask == "x" || substr($trab_saida, $iz, 1) != "0")
           {
               $ix = strlen($trab_mask) + 1;
           }
           else
           {
               $trab_saida = substr($trab_saida, 0, $iz) . substr($trab_saida, $iz + 1);
           }
      }
      $nm_campo = $str_highlight_ini . $trab_saida . $str_highlight_ini;
   } 
function getBrasaoClube($id){
$_SESSION['scriptcase']['cons_jogos_brasil']['contr_erro'] = 'on';
  
    $brasaoTime = '';
    $sql = "select brasao_url 
	        FROM clubes 
			WHERE clube_id = $id";
	
	 
      $nm_select = $sql; 
      $_SESSION['scriptcase']['sc_sql_ult_comando'] = $nm_select; 
      $_SESSION['scriptcase']['sc_sql_ult_conexao'] = ''; 
      $this->rs = array();
      if ($SCrx = $this->Db->Execute($nm_select)) 
      { 
          $SCy = 0; 
          $nm_count = $SCrx->FieldCount();
          while (!$SCrx->EOF)
          { 
                 for ($SCx = 0; $SCx < $nm_count; $SCx++)
                 { 
                        $this->rs[$SCy] [$SCx] = $SCrx->fields[$SCx];
                 }
                 $SCy++; 
                 $SCrx->MoveNext();
          } 
          $SCrx->Close();
      } 
      elseif (isset($GLOBALS["NM_ERRO_IBASE"]) && $GLOBALS["NM_ERRO_IBASE"] != 1)  
      { 
          $this->rs = false;
          $this->rs_erro = $this->Db->ErrorMsg();
      } 
;
	
	$brasaoTime = $this->rs [0][0]; 
		
	return $brasaoTime;
$_SESSION['scriptcase']['cons_jogos_brasil']['contr_erro'] = 'off';
}
function getNomeClube($id){
$_SESSION['scriptcase']['cons_jogos_brasil']['contr_erro'] = 'on';
  
    $nomeTime = '';
    $sql = "select nome_clube
	        FROM clubes 
			WHERE clube_id = $id";
	
	 
      $nm_select = $sql; 
      $_SESSION['scriptcase']['sc_sql_ult_comando'] = $nm_select; 
      $_SESSION['scriptcase']['sc_sql_ult_conexao'] = ''; 
      $this->rs = array();
      if ($SCrx = $this->Db->Execute($nm_select)) 
      { 
          $SCy = 0; 
          $nm_count = $SCrx->FieldCount();
          while (!$SCrx->EOF)
          { 
                 for ($SCx = 0; $SCx < $nm_count; $SCx++)
                 { 
                        $this->rs[$SCy] [$SCx] = $SCrx->fields[$SCx];
                 }
                 $SCy++; 
                 $SCrx->MoveNext();
          } 
          $SCrx->Close();
      } 
      elseif (isset($GLOBALS["NM_ERRO_IBASE"]) && $GLOBALS["NM_ERRO_IBASE"] != 1)  
      { 
          $this->rs = false;
          $this->rs_erro = $this->Db->ErrorMsg();
      } 
;
	
	$nomeTime = $this->rs [0][0]; 
		
	return $nomeTime;
$_SESSION['scriptcase']['cons_jogos_brasil']['contr_erro'] = 'off';
}
function inserirPontosAposta($aposta,$pontosJg){
$_SESSION['scriptcase']['cons_jogos_brasil']['contr_erro'] = 'on';
  

    $cmd = "update apostas set pontos = $pontosJg, l_calc_aposta = 1 where aposta_id = $aposta";
    
     $nm_select = $cmd; 
         $_SESSION['scriptcase']['sc_sql_ult_comando'] = $nm_select;
      $_SESSION['scriptcase']['sc_sql_ult_conexao'] = ''; 
         $rf = $this->Db->Execute($nm_select);
         if ($rf === false)
         {
             $this->Erro->mensagem (__FILE__, __LINE__, "banco", $this->Ini->Nm_lang['lang_errm_dber'], $this->Db->ErrorMsg());
             if ($this->Ini->sc_tem_trans_banco)
             {
                 $this->Db->RollbackTrans(); 
                 $this->Ini->sc_tem_trans_banco = false;
             }
             exit;
         }
         $rf->Close();
      ;
$_SESSION['scriptcase']['cons_jogos_brasil']['contr_erro'] = 'off';
}
function calcPontosApostas($apTimeCasaPlacar,$apTimeVisiPlacar,$jgTimeCasaPlacar,$jgTimeVisiPlacar){
$_SESSION['scriptcase']['cons_jogos_brasil']['contr_erro'] = 'on';
  
	$pontosJogo = 0;
	$apResultado = '';
	$jogoResultado = '';

    if($apTimeCasaPlacar < $apTimeVisiPlacar){
        $apResultado = 'time_casa_vencedor';
    }else if($apTimeCasaPlacar > $apTimeVisiPlacar){
        $apResultado = 'time_visitante_vencedor';
    }else{
        $apResultado = 'empate';
    }

    if($jgTimeCasaPlacar < $jgTimeVisiPlacar){
        $jogoResultado = 'time_casa_vencedor';
    }else if($jgTimeCasaPlacar > $jgTimeVisiPlacar){
        $jogoResultado = 'time_visitante_vencedor';
    }else{
        $jogoResultado = 'empate';
    }

    if($apTimeCasaPlacar == $jgTimeCasaPlacar and $apTimeVisiPlacar == $jgTimeVisiPlacar){
        $pontosJogo = 5;
    }else if($jogoResultado == $apResultado){
        $pontosJogo = 3;
    }else{
        $pontosJogo = 0;
    }
    return $pontosJogo;




$_SESSION['scriptcase']['cons_jogos_brasil']['contr_erro'] = 'off';
}
function CalcApostas(){
$_SESSION['scriptcase']['cons_jogos_brasil']['contr_erro'] = 'on';
  
    $sql = "SELECT
			   apostas.aposta_id,
			   apostas.dt_hr_aposta,
			   apostas.login_id,
			   apostas.time_casa_placar,
			   apostas.time_visitante_placar,
			   apostas.pontos,
			   jogos.jogo_id,
			   jogos.data_jogo,
			   jogos.time_casa_id,
			   jogos.time_visitante_id,
			   jogos.time_visitante_placar,
			   jogos.time_casa_placar,
			   jogos.horario,
			   jogos.rodada,
			   jogos.competicao_id,
			   apostas.l_calc_aposta
		FROM
   			   apostas INNER JOIN jogos ON apostas.id_jogo_api = jogos.jogo_api_id           
		WHERE 
		       jogos.data_jogo >= curdate() - 2";

     
      $nm_select = $sql; 
      $_SESSION['scriptcase']['sc_sql_ult_comando'] = $nm_select; 
      $_SESSION['scriptcase']['sc_sql_ult_conexao'] = ''; 
      $this->rs = array();
      if ($SCrx = $this->Db->Execute($nm_select)) 
      { 
          $SCy = 0; 
          $nm_count = $SCrx->FieldCount();
          while (!$SCrx->EOF)
          { 
                 for ($SCx = 0; $SCx < $nm_count; $SCx++)
                 { 
                        $this->rs[$SCy] [$SCx] = $SCrx->fields[$SCx];
                 }
                 $SCy++; 
                 $SCrx->MoveNext();
          } 
          $SCrx->Close();
      } 
      elseif (isset($GLOBALS["NM_ERRO_IBASE"]) && $GLOBALS["NM_ERRO_IBASE"] != 1)  
      { 
          $this->rs = false;
          $this->rs_erro = $this->Db->ErrorMsg();
      } 
;


    return $this->rs ;
$_SESSION['scriptcase']['cons_jogos_brasil']['contr_erro'] = 'off';
}
function estado(){
$_SESSION['scriptcase']['cons_jogos_brasil']['contr_erro'] = 'on';
  
	
	$array = array(
    "mg" => "Minas Gerais",
    "sp" => "S??o Paulo",
	"rj" => "Rio de Janeiro",
	"es" => "Espirito Santo",
);
	return $array;
	

$_SESSION['scriptcase']['cons_jogos_brasil']['contr_erro'] = 'off';
}
function buscarClubesComp($comp){
$_SESSION['scriptcase']['cons_jogos_brasil']['contr_erro'] = 'on';
  	
	$clubes = 0;
	$sql = "select clube_id 
	        FROM clubes 
			WHERE competicao_id = $comp";
	
	 
      $nm_select = $sql; 
      $_SESSION['scriptcase']['sc_sql_ult_comando'] = $nm_select; 
      $_SESSION['scriptcase']['sc_sql_ult_conexao'] = ''; 
      $this->rs = array();
      if ($SCrx = $this->Db->Execute($nm_select)) 
      { 
          $SCy = 0; 
          $nm_count = $SCrx->FieldCount();
          while (!$SCrx->EOF)
          { 
                 for ($SCx = 0; $SCx < $nm_count; $SCx++)
                 { 
                        $this->rs[$SCy] [$SCx] = $SCrx->fields[$SCx];
                 }
                 $SCy++; 
                 $SCrx->MoveNext();
          } 
          $SCrx->Close();
      } 
      elseif (isset($GLOBALS["NM_ERRO_IBASE"]) && $GLOBALS["NM_ERRO_IBASE"] != 1)  
      { 
          $this->rs = false;
          $this->rs_erro = $this->Db->ErrorMsg();
      } 
;
	
	$nat = $this->rs ; 
	foreach($nat as $idClubes){
		$idClube = $idClubes[0];
		$clubes = util_incr_valor($clubes,$idClube,",");		
	
	}
	
	return $clubes;
$_SESSION['scriptcase']['cons_jogos_brasil']['contr_erro'] = 'off';
}
function buscarNomeUsuario($usuar){
$_SESSION['scriptcase']['cons_jogos_brasil']['contr_erro'] = 'on';
  	

	$sql = "select name 
	        FROM s_users 
			WHERE login = $usuar";
	
	 
      $nm_select = $sql; 
      $_SESSION['scriptcase']['sc_sql_ult_comando'] = $nm_select; 
      $_SESSION['scriptcase']['sc_sql_ult_conexao'] = ''; 
      $this->rs = array();
      if ($SCrx = $this->Db->Execute($nm_select)) 
      { 
          $SCy = 0; 
          $nm_count = $SCrx->FieldCount();
          while (!$SCrx->EOF)
          { 
                 for ($SCx = 0; $SCx < $nm_count; $SCx++)
                 { 
                        $this->rs[$SCy] [$SCx] = $SCrx->fields[$SCx];
                 }
                 $SCy++; 
                 $SCrx->MoveNext();
          } 
          $SCrx->Close();
      } 
      elseif (isset($GLOBALS["NM_ERRO_IBASE"]) && $GLOBALS["NM_ERRO_IBASE"] != 1)  
      { 
          $this->rs = false;
          $this->rs_erro = $this->Db->ErrorMsg();
      } 
;
	
	$aUser = $this->rs [0][0]; 
		
	return $aUser;
$_SESSION['scriptcase']['cons_jogos_brasil']['contr_erro'] = 'off';
}
function getGrupoUsuar($usuar){
$_SESSION['scriptcase']['cons_jogos_brasil']['contr_erro'] = 'on';
  	

	$sql = "select group_id 
	        FROM s_users_groups 
			WHERE login = $usuar";
	
	 
      $nm_select = $sql; 
      $_SESSION['scriptcase']['sc_sql_ult_comando'] = $nm_select; 
      $_SESSION['scriptcase']['sc_sql_ult_conexao'] = ''; 
      $this->rs = array();
      if ($SCrx = $this->Db->Execute($nm_select)) 
      { 
          $SCy = 0; 
          $nm_count = $SCrx->FieldCount();
          while (!$SCrx->EOF)
          { 
                 for ($SCx = 0; $SCx < $nm_count; $SCx++)
                 { 
                        $this->rs[$SCy] [$SCx] = $SCrx->fields[$SCx];
                 }
                 $SCy++; 
                 $SCrx->MoveNext();
          } 
          $SCrx->Close();
      } 
      elseif (isset($GLOBALS["NM_ERRO_IBASE"]) && $GLOBALS["NM_ERRO_IBASE"] != 1)  
      { 
          $this->rs = false;
          $this->rs_erro = $this->Db->ErrorMsg();
      } 
;
		
	return $this->rs ;
$_SESSION['scriptcase']['cons_jogos_brasil']['contr_erro'] = 'off';
}
function verificaApostas($jogoId,$user,$bolaoId){
$_SESSION['scriptcase']['cons_jogos_brasil']['contr_erro'] = 'on';
  
	$sql = "select aposta_id 
	        FROM apostas 
			WHERE id_jogo_api = $jogoId and login_id = $user and bolao_id=$bolaoId";
	
	 
      $nm_select = $sql; 
      $_SESSION['scriptcase']['sc_sql_ult_comando'] = $nm_select; 
      $_SESSION['scriptcase']['sc_sql_ult_conexao'] = ''; 
      $this->rs = array();
      if ($SCrx = $this->Db->Execute($nm_select)) 
      { 
          $SCy = 0; 
          $nm_count = $SCrx->FieldCount();
          while (!$SCrx->EOF)
          { 
                 for ($SCx = 0; $SCx < $nm_count; $SCx++)
                 { 
                        $this->rs[$SCy] [$SCx] = $SCrx->fields[$SCx];
                 }
                 $SCy++; 
                 $SCrx->MoveNext();
          } 
          $SCrx->Close();
      } 
      elseif (isset($GLOBALS["NM_ERRO_IBASE"]) && $GLOBALS["NM_ERRO_IBASE"] != 1)  
      { 
          $this->rs = false;
          $this->rs_erro = $this->Db->ErrorMsg();
      } 
;
	
	
	return $this->rs ;
$_SESSION['scriptcase']['cons_jogos_brasil']['contr_erro'] = 'off';
}
function buscarNomeClube($idClube){
$_SESSION['scriptcase']['cons_jogos_brasil']['contr_erro'] = 'on';
  	

	$sql = "select nome_clube 
	        FROM clubes 
			WHERE clube_id = $idClube";
	
	 
      $nm_select = $sql; 
      $_SESSION['scriptcase']['sc_sql_ult_comando'] = $nm_select; 
      $_SESSION['scriptcase']['sc_sql_ult_conexao'] = ''; 
      $this->rs = array();
      if ($SCrx = $this->Db->Execute($nm_select)) 
      { 
          $SCy = 0; 
          $nm_count = $SCrx->FieldCount();
          while (!$SCrx->EOF)
          { 
                 for ($SCx = 0; $SCx < $nm_count; $SCx++)
                 { 
                        $this->rs[$SCy] [$SCx] = $SCrx->fields[$SCx];
                 }
                 $SCy++; 
                 $SCrx->MoveNext();
          } 
          $SCrx->Close();
      } 
      elseif (isset($GLOBALS["NM_ERRO_IBASE"]) && $GLOBALS["NM_ERRO_IBASE"] != 1)  
      { 
          $this->rs = false;
          $this->rs_erro = $this->Db->ErrorMsg();
      } 
;
	
	$clubeNome = $this->rs [0][0]; 
		
	return $clubeNome;
$_SESSION['scriptcase']['cons_jogos_brasil']['contr_erro'] = 'off';
}
function buscarNomeComp($idComp){
$_SESSION['scriptcase']['cons_jogos_brasil']['contr_erro'] = 'on';
  	

	$sql = "select nome_competicao 
	        FROM competicoes
			WHERE competicao_id = $idComp";
	
	 
      $nm_select = $sql; 
      $_SESSION['scriptcase']['sc_sql_ult_comando'] = $nm_select; 
      $_SESSION['scriptcase']['sc_sql_ult_conexao'] = ''; 
      $this->rs = array();
      if ($SCrx = $this->Db->Execute($nm_select)) 
      { 
          $SCy = 0; 
          $nm_count = $SCrx->FieldCount();
          while (!$SCrx->EOF)
          { 
                 for ($SCx = 0; $SCx < $nm_count; $SCx++)
                 { 
                        $this->rs[$SCy] [$SCx] = $SCrx->fields[$SCx];
                 }
                 $SCy++; 
                 $SCrx->MoveNext();
          } 
          $SCrx->Close();
      } 
      elseif (isset($GLOBALS["NM_ERRO_IBASE"]) && $GLOBALS["NM_ERRO_IBASE"] != 1)  
      { 
          $this->rs = false;
          $this->rs_erro = $this->Db->ErrorMsg();
      } 
;
	
	$compNome = $this->rs [0][0]; 
		
	return $compNome;
$_SESSION['scriptcase']['cons_jogos_brasil']['contr_erro'] = 'off';
}
function getIdJogoCopaMundo(){
$_SESSION['scriptcase']['cons_jogos_brasil']['contr_erro'] = 'on';
  
    $sql = "select jogo_api_id
	        from jogos
			where competicao_id = 7";
     
      $nm_select = $sql; 
      $_SESSION['scriptcase']['sc_sql_ult_comando'] = $nm_select; 
      $_SESSION['scriptcase']['sc_sql_ult_conexao'] = ''; 
      $this->rs = array();
      if ($SCrx = $this->Db->Execute($nm_select)) 
      { 
          $SCy = 0; 
          $nm_count = $SCrx->FieldCount();
          while (!$SCrx->EOF)
          { 
                 for ($SCx = 0; $SCx < $nm_count; $SCx++)
                 { 
                        $this->rs[$SCy] [$SCx] = $SCrx->fields[$SCx];
                 }
                 $SCy++; 
                 $SCrx->MoveNext();
          } 
          $SCrx->Close();
      } 
      elseif (isset($GLOBALS["NM_ERRO_IBASE"]) && $GLOBALS["NM_ERRO_IBASE"] != 1)  
      { 
          $this->rs = false;
          $this->rs_erro = $this->Db->ErrorMsg();
      } 
;
    $idJogo = $this->rs ;
	return $idJogo;
$_SESSION['scriptcase']['cons_jogos_brasil']['contr_erro'] = 'off';
}
}

?>
