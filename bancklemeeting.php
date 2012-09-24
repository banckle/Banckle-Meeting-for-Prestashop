<?php
if (!defined('_PS_VERSION_'))
	exit;

class BanckleMeeting extends Module 
{                                                                            
	private $_html = '';
	private $_postErrors = array();
	private $url = "https://apps.banckle.com/meeting/api/widget"; 
                                                                            
	function __construct() 
	{
		$this->name = 'bancklemeeting';
		$this->tab = 'other';
		$this->version = '1.0';
		$this->author = 'Masood Anwer';
		parent::__construct();
		$this->displayName = $this->l('Banckle Meeting');
		$this->description = $this->l('This module provides your customers with online demos about your products to increase number of items in their shopping cart.');
	}
                                                                            
	public function install() 
	{
		parent::install();
		Configuration::updateValue('BANCKLE_MEETING_WIDGET_WIDTH', '191');
		Configuration::updateValue('BANCKLE_MEETING_WIDGET_HEIGHT', '455');
		if(!$this->registerHook('leftColumn')) 
			return false;
		return true;
	}
	
	public function uninstall()
	{
	  if (!parent::uninstall()
		|| !Configuration::deleteByName('BANCKLE_MEETING_WIDGET_CODE') 
		|| !Configuration::deleteByName('BANCKLE_MEETING_WIDGET_WIDTH')
		|| !Configuration::deleteByName('BANCKLE_MEETING_WIDGET_HEIGHT')
		|| !Configuration::deleteByName('BANCKLE_MEETING_WIDGET_LOGO'))   
		return false;
	  return true;
	} 

	private function _postValidation()
	{
		if (!Tools::getValue('widget_code')) 
			$this->_postErrors[] = $this->l('Widget ID is required.');	
		
		if (!Tools::getValue('widget_width')) 
			$this->_postErrors[] = $this->l('Widget Width is required.');
				
		if (!Tools::getValue('widget_height'))
			$this->_postErrors[] = $this->l('Widget Height is required.');
	}
                                                                            
	public function getContent() 
	{                                                                        
		if(Tools::isSubmit('submit')) 
		{                                                                       	
			$this->_postValidation();
			
			if (!sizeof($this->_postErrors))
			{
				Configuration::updateValue('BANCKLE_MEETING_WIDGET_CODE', Tools::getValue('widget_code'));
				Configuration::updateValue('BANCKLE_MEETING_WIDGET_WIDTH', Tools::getValue('widget_width'));
				Configuration::updateValue('BANCKLE_MEETING_WIDGET_HEIGHT', Tools::getValue('widget_height'));
				Configuration::updateValue('BANCKLE_MEETING_WIDGET_LOGO', Tools::getValue('widget_logo'));
				$this->_html .= '<div class="conf"><img src="../img/admin/ok2.png" alt="">'.$this->l('Configuration Saved.').'</div>';	
			} else {
				$this->_html .= '<div class="error"><span style="float:right"><a id="hideError" href=""><img alt="X" src="../img/admin/close.png"></a></span><img src="../img/admin/error2.png"><br>';
				$this->_html .= '<ul>';
				foreach ($this->_postErrors AS $error)
					$this->_html .= '<li>'.$error.'</li>';
				$this->_html .= '</ul>';
				$this->_html .= '</div>';
			}
		}
																				
		$this->_generateForm();
		return $this->_html;
	}
                                                                            
	private function _generateForm() 
	{																			
		$widgetCode   = Configuration::get('BANCKLE_MEETING_WIDGET_CODE');
		$widgetWidth  = Configuration::get('BANCKLE_MEETING_WIDGET_WIDTH');
		$widgetHeight = Configuration::get('BANCKLE_MEETING_WIDGET_HEIGHT');
		$widgetLogo   = Configuration::get('BANCKLE_MEETING_WIDGET_LOGO');
		
		if ($widgetLogo == "true")
			$radioYes = "checked=checked";
		$radioNo = "checked=checked";
																				
		$this->_html .= "<fieldset>";
		$this->_html .= "<legend>Banckle Meeting</legend>";
		$this->_html .= '<form action="'.$_SERVER['REQUEST_URI'].'" method="post">';
		$this->_html .= '<label>'.$this->l('Widget ID: ').'</label>';
		$this->_html .= '<div class="margin-form">';
		$this->_html .= '<input type="text" name="widget_code" value="'.$widgetCode.'" size="40" >&nbsp;<sup>*</sup>';
		$this->_html .= '</div>';
		$this->_html .= '<label>'.$this->l('Widget Width: ').'</label>';
		$this->_html .= '<div class="margin-form">';
		$this->_html .= '<input type="text" name="widget_width" value="'.$widgetWidth.'" size="3" maxlength="3" >&nbsp;<sup>*</sup>';
		$this->_html .= '</div>';
		$this->_html .= '<label>'.$this->l('Widget Height: ').'</label>';
		$this->_html .= '<div class="margin-form">';
		$this->_html .= '<input type="text" name="widget_height" value="'.$widgetHeight.'" size="3" maxlength="3" >&nbsp;<sup>*</sup>';
		$this->_html .= '</div>';
		$this->_html .= '<label>'.$this->l('Show Logo: ').'</label>';
		$this->_html .= '<div class="margin-form">';
		$this->_html .= '<input type="radio" name="widget_logo" value="true" '.$radioYes.' >Yes &nbsp;&nbsp;';
		$this->_html .= '<input type="radio" name="widget_logo" value="false" '.$radioNo.' >No';
		$this->_html .= '</div>';
		$this->_html .= '<div class="margin-form">';
		$this->_html .= '<input type="submit" name="submit" ';
		$this->_html .= 'value="'.$this->l('Save Configuration').'" class="button" />';
		$this->_html .= '</div>';
		$this->_html .= '<div class="small"><sup>*</sup> Required field</div>';
		$this->_html .= '</form>';
		$this->_html .= "</fieldset>";
	}
	
	private function _curlRequest($url, $method="GET", $headerType="XML", $xmlsrc="") 
	{
		$method = strtoupper($method);
		$headerType = strtoupper($headerType);
		$session = curl_init();
		curl_setopt($session, CURLOPT_URL, $url);
		if ($method == "GET") {
		  curl_setopt($session, CURLOPT_HTTPGET, 1);
		} else {
			curl_setopt($session, CURLOPT_POST, 1);
			curl_setopt($session, CURLOPT_POSTFIELDS, $xmlsrc);
			curl_setopt($session, CURLOPT_CUSTOMREQUEST, $method);
		}
		
		curl_setopt($session, CURLOPT_HEADER, false);
		
		if ($headerType == "XML")
			curl_setopt($session, CURLOPT_HTTPHEADER, array('Accept: application/xml', 'Content-Type: application/xml'));
		curl_setopt($session, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));

		curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
		if (preg_match("/^(https)/i", $url))
			curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
		$result = curl_exec($session);
		curl_close($session);
		return $result;
	}
                                                                            
	public function hookLeftColumn() 
	{																			
		$code   = Configuration::get('BANCKLE_MEETING_WIDGET_CODE');
		$width  = Configuration::get('BANCKLE_MEETING_WIDGET_WIDTH');
		$height = Configuration::get('BANCKLE_MEETING_WIDGET_HEIGHT');
		$logo   = Configuration::get('BANCKLE_MEETING_WIDGET_LOGO');

		$jsonText = $this->_curlRequest('https://apps.banckle.com/meeting/api/widget?wid='.$code, "GET", "JSON", "");
		$arr = array();
		$jsonError = false;
	
		if ($jsonText !== false) 
		{
			$arr = json_decode($jsonText, true);
			switch (json_last_error ()) 
			{
				case JSON_ERROR_NONE:
					$jsonError = false;
					break;
				default:
					$jsonError = true;
					break;
			}
	
			if ($code && !empty($code))
			{
				global $smarty;
				$smarty->assign('url', $this->url);
				$smarty->assign('code', $code);
				$smarty->assign('width', $width);
				$smarty->assign('height', $height);
				$smarty->assign('logo', $logo);
				$smarty->assign('jsonError', $jsonError);
				return $this->display(__FILE__, 'bancklemeeting.tpl');
			}	
		}
	}

	public function hookRightColumn($params) 
	{
		return $this->hookLeftColumn($params);
	}
                                                                            
} 
?>