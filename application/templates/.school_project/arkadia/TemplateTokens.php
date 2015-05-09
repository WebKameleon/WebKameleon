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

	protected $naglowek_h1=false;
	protected function box_title() {
		if ($this->webtd['level']==1 && !$this->naglowek_h1 && $this->webtd['page_id']>0) {
                        $this->naglowek_h1 = true;
                        return '<h1 class="title">'.$this->webtd['title'].'</h1>';
                } elseif ($this->webtd['level']==3 || $this->webtd['level']==4) {
                        return '<h3 class="title">'.$this->webtd['title'].'</h3>';
                }                
		return '<h2 class="title">'.$this->webtd['title'].'</h2>';
	}

}
