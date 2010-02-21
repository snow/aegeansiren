<?php

class AgsListView extends CListView
{
    public $itemView = '_listItem';
    public $template = '{items}{pager}';
    public $ajaxUpdate = false;
    public $cssFile = false;
    public $itemsCssClass = 'ls';
    public $pagerCssClass = 's-pgr-ctr';
    public $enableSorting = false;

    public function getId()
    {
        return $this->htmlOptions['id']?$this->htmlOptions['id']:parent::getId();
    }

    public function registerClientScript(){}
}