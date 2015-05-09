<?php
class TemplateTokens extends Tokens {

	protected function webtd_style() {
		if (strlen($this->webtd['costxt'])) parse_str($this->webtd['costxt']);

		if (strlen($this->webtd['bgcolor']) || strlen($this->webtd['width']) || strlen($this->webtd['align']) || strlen($this->webtd['valign']) || isset($tdstyle)) {

			$_ret=' style="';

			if (strlen($this->webtd['bgcolor'])) $_ret.= "background-color:#".$this->webtd['bgcolor'].";";
			if (strlen($this->webtd['width'])) $_ret.= "width:".$this->webtd['width']."px;";
			if (strlen($this->webtd['align'])) $_ret.= "text-align:".$this->webtd['align'].";";
			if (strlen($this->webtd['valign'])) $_ret.= "vertical-align:".$this->webtd['valign'].";";
			if (isset($tdstyle) && strlen($tdstyle)) $_ret.= $tdstyle;

			$_ret.='"';

		}
	}

	protected function box_title() {
		$naglowek = isset($this->webtd['naglowek']) && strlen($this->webtd['naglowek']) ? $this->webtd['naglowek'] : "h1";

		$prep='';
		$uimages=Bootstrap::$main->session('uimages');
		if ($this->webtd['bgimg'])
			$prep = "style=\"padding:10px 0px 10px 60px;background:transparent url(".$uimages."/" . $this->webtd['bgimg'] .") no-repeat center left \"";
		if (strlen($this->webtd['title'])) {
       			if ($this->webtd['next']) return "<".$naglowek." ".$prep." class=\"title\"><a href=\"".$this->webtd['next_link']."\">".$this->webtd['title']."</a></".$naglowek.">";
				return "<".$naglowek." ".$prep." class=\"title\" >".$this->webtd['title']."</".$naglowek.">";
		}
	}

}