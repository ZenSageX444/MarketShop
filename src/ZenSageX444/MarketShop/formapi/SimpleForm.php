<?php

namespace ZenSageX444\MarketShop\formapi;

use pocketmine\form\FormValidationException;

class SimpleForm extends Form {

    const IMAGE_TYPE_PATH = 0;
    const IMAGE_TYPE_URL = 1;

    private string $content = "";

    private array $labelMap = [];

    public function __construct(?callable $callable) {
        parent::__construct($callable);
        $this->data["type"] = "form";
        $this->data["title"] = "";
        $this->data["content"] = $this->content;
        $this->data["buttons"] = [];
    }

    public function processData(&$data) : void {
        if($data !== null) {
            if(!is_int($data)) {
                throw new FormValidationException("Expected an integer response, got " . gettype($data));
            }
            $count = count($this->data["buttons"]);
            if($data >= $count || $data < 0) {
                throw new FormValidationException("Button $data does not exist");
            }
            $data = $this->labelMap[$data] ?? null;
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

    public function addButton(string $text, int $imageType = -1, string $imagePath = "", ?string $label = null) : self {
        $content = ["text" => $text];
        if($imageType !== -1) {
            $content["image"]["type"] = $imageType === 0 ? "path" : "url";
            $content["image"]["data"] = $imagePath;
        }
        $this->data["buttons"][] = $content;
        $this->labelMap[] = $label ?? count($this->labelMap);
        return $this;
    }

}
