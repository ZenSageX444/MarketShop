<?php

namespace ZenSageX444\MarketShop\formapi;

use pocketmine\form\FormValidationException;

class ModalForm extends Form {

    private string $content = "";

    public function __construct(?callable $callable) {
        parent::__construct($callable);
        $this->data["type"] = "modal";
        $this->data["title"] = "";
        $this->data["content"] = $this->content;
        $this->data["button1"] = "";
        $this->data["button2"] = "";
    }

    public function processData(&$data) : void {
        if(!is_bool($data)) {
            throw new FormValidationException("Expected a boolean response, got " . gettype($data));
        }
    }

    public function setTitle(string $title) : self {
        $this->data["title"] = $title;
        return $this;
    }

    public function getTitle() : string {
        return $this->data["title"];
    }

    public function getContent() : string {
        return $this->data["content"];
    }

    public function setContent(string $content) : self {
        $this->data["content"] = $content;
        return $this;
    }

    public function setButton1(string $text) : self {
        $this->data["button1"] = $text;
        return $this;
    }

    public function getButton1() : string {
        return $this->data["button1"];
    }

    public function setButton2(string $text) : self {
        $this->data["button2"] = $text;
        return $this;
    }

    public function getButton2() : string {
        return $this->data["button2"];
    }
}
