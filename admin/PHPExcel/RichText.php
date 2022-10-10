<?php
namespace PhpOffice\PhpSpreadsheet\RichText;

/**
 * PHPExcel
 *
 * Copyright (c) 2006 - 2014 PHPExcel
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   PHPExcel
 * @package    PHPExcel_RichText
 * @copyright  Copyright (c) 2006 - 2014 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
 * @version    1.8.0, 2014-03-02
 */
/**
 * PHPExcel_RichText
 *
 * @category   PHPExcel
 * @package    PHPExcel_RichText
 * @copyright  Copyright (c) 2006 - 2014 PHPExcel (http://www.codeplex.com/PHPExcel)
 */
class RichText implements \PhpOffice\PhpSpreadsheet\IComparable
{
    /**
     * Rich text elements
     *
     * @var \PhpOffice\PhpSpreadsheet\RichText\ITextElement[]
     */
    private $_richTextElements = array();

    /**
     * Create a new PHPExcel_RichText instance
     *
     * @param \PhpOffice\PhpSpreadsheet\Cell\Cell $pCell
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function __construct(\PhpOffice\PhpSpreadsheet\Cell\Cell $pCell = \null)
    {
        // Rich-Text string attached to cell?
        if ($pCell !== \NULL) {
            // Add cell text and style
            if ($pCell->getValue() != "") {
                $objRun = new \PhpOffice\PhpSpreadsheet\RichText\Run($pCell->getValue());
                $objRun->setFont(clone $pCell->getParent()->getStyle($pCell->getCoordinate())->getFont());
                $this->addText($objRun);
            }

            // Set parent value
            $pCell->setValueExplicit($this, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        }
    }

    /**
     * Add text
     *
     * @param \PhpOffice\PhpSpreadsheet\RichText\ITextElement $pText Rich text element
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return \PhpOffice\PhpSpreadsheet\RichText\RichText
     */
    public function addText(\PhpOffice\PhpSpreadsheet\RichText\ITextElement $pText = \null)
    {
        $this->_richTextElements[] = $pText;
        return $this;
    }

    /**
     * Create text
     *
     * @param string $pText Text
     * @return \PhpOffice\PhpSpreadsheet\RichText\TextElement
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function createText($pText = '')
    {
        $objText = new \PhpOffice\PhpSpreadsheet\RichText\TextElement($pText);
        $this->addText($objText);
        return $objText;
    }

    /**
     * Create text run
     *
     * @param string $pText Text
     * @return \PhpOffice\PhpSpreadsheet\RichText\Run
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function createTextRun($pText = '')
    {
        $objText = new \PhpOffice\PhpSpreadsheet\RichText\Run($pText);
        $this->addText($objText);
        return $objText;
    }

    /**
     * Get plain text
     *
     * @return string
     */
    public function getPlainText()
    {
        // Return value
        $returnValue = '';

        // Loop through all PHPExcel_RichText_ITextElement
        foreach ($this->_richTextElements as $text) {
            $returnValue .= $text->getText();
        }

        // Return
        return $returnValue;
    }

    /**
     * Convert to string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getPlainText();
    }

    /**
     * Get Rich Text elements
     *
     * @return \PhpOffice\PhpSpreadsheet\RichText\ITextElement[]
     */
    public function getRichTextElements()
    {
        return $this->_richTextElements;
    }

    /**
     * Set Rich Text elements
     *
     * @param \PhpOffice\PhpSpreadsheet\RichText\ITextElement[] $pElements Array of elements
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return \PhpOffice\PhpSpreadsheet\RichText\RichText
     */
    public function setRichTextElements($pElements = \null)
    {
        if (\is_array($pElements)) {
            $this->_richTextElements = $pElements;
        } else {
            throw new \PhpOffice\PhpSpreadsheet\Exception("Invalid PHPExcel_RichText_ITextElement[] array passed.");
        }
        return $this;
    }

    /**
     * Get hash code
     *
     * @return string    Hash code
     */
    public function getHashCode()
    {
        $hashElements = '';
        foreach ($this->_richTextElements as $element) {
            $hashElements .= $element->getHashCode();
        }

        return \md5(
              $hashElements
            . __CLASS__
        );
    }

    /**
     * Implement PHP __clone to create a deep clone, not just a shallow copy.
     */
    public function __clone()
    {
        $vars = \get_object_vars($this);
        foreach ($vars as $key => $value) {
            if (\is_object($value)) {
                $this->$key = clone $value;
            } else {
                $this->$key = $value;
            }
        }
    }
}
