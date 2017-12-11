<?php

namespace Villermen\FeedProcessing;

/**
 * Because face it, the one PHP provides is just nasty.
 */
class ActuallySimpleXmlElement implements \ArrayAccess
{
    public $tagName;
    public $attributes = [];
    public $text = "";

    /** @var ActuallySimpleXmlElement[] */
    public $children = [];

    /**
     * @param \DOMNode $node
     * @return ActuallySimpleXmlElement
     */
    public static function fromDomNode(\DOMNode $node)
    {
        $element = new ActuallySimpleXmlElement();

        $element->tagName = $node->nodeName;

        /** @var \DOMAttr $attribute */
        foreach ($node->attributes as $attribute) {
            $element->attributes[$attribute->name] = $attribute->value;
        }

        /** @var \DOMNode $childNode */
        foreach ($node->childNodes as $childNode) {
            switch ($childNode->nodeType) {
                case XML_ELEMENT_NODE:
                    $element->children[] = self::fromDomNode($childNode);
                    break;

                case XML_TEXT_NODE:
                    $element->text = trim($childNode->textContent);
                    break;

                case XML_CDATA_SECTION_NODE:
                    $element->text = $childNode->textContent;
                    break;
            }
        }

        return $element;
    }

    /**
     * @param string $tagName
     * @return ActuallySimpleXmlElement[]
     */
    public function get($tagName)
    {
        return array_filter($this->children, function($child) use ($tagName) {
            /** @var ActuallySimpleXmlElement $child */
            return $child->tagName === $tagName;
        });
    }

    /**
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return count($this->get($offset)) > 0;
    }

    /**
     * @param string $offset
     * @return ActuallySimpleXmlElement The first matched child.
     * @throws \Exception When no direct children with the given tag name exist.
     */
    public function offsetGet($offset)
    {
        $matchedChildren = $this->get($offset);

        if (count($matchedChildren) === 0) {
            throw new \Exception("No direct children with tag name \"{$offset}\" exist.");
        }

        return current($matchedChildren);
    }

    public function offsetSet($offset, $value)
    {
        throw new \Exception("Invalid operation.");
    }

    public function offsetUnset($offset)
    {
        throw new \Exception("Invalid operation.");
    }
}
