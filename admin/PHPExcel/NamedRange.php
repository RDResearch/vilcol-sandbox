<?php
namespace PhpOffice\PhpSpreadsheet;

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
 * @package    PHPExcel
 * @copyright  Copyright (c) 2006 - 2014 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version    1.8.0, 2014-03-02
 */
/**
 * PHPExcel_NamedRange
 *
 * @category   PHPExcel
 * @package    PHPExcel
 * @copyright  Copyright (c) 2006 - 2014 PHPExcel (http://www.codeplex.com/PHPExcel)
 */
class NamedRange
{
	/**
	 * Range name
	 *
	 * @var string
	 */
	private $_name;

	/**
  * Worksheet on which the named range can be resolved
  *
  * @var \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
  */
 private $_worksheet;

	/**
	 * Range of the referenced cells
	 *
	 * @var string
	 */
	private $_range;

	/**
	 * Is the named range local? (i.e. can only be used on $this->_worksheet)
	 *
	 * @var bool
	 */
	private $_localOnly;

	/**
  * Scope
  *
  * @var \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
  */
 private $_scope;

    /**
     * Create a new NamedRange
     *
     * @param string $pName
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $pWorksheet
     * @param string $pRange
     * @param bool $pLocalOnly
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet|null $pScope Scope. Only applies when $pLocalOnly = true. Null for global scope.
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function __construct($pName = \null, \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $pWorksheet, $pRange = 'A1', $pLocalOnly = \false, $pScope = \null)
    {
    	// Validate data
    	if (($pName === \NULL) || ($pWorksheet === \NULL) || ($pRange === \NULL)) {
    		throw new \PhpOffice\PhpSpreadsheet\Exception('Parameters can not be null.');
    	}

    	// Set local members
    	$this->_name 		= $pName;
    	$this->_worksheet 	= $pWorksheet;
    	$this->_range 		= $pRange;
    	$this->_localOnly 	= $pLocalOnly;
    	$this->_scope 		= ($pLocalOnly == \true) ?
								(($pScope == \null) ? $pWorksheet : $pScope) : \null;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName() {
    	return $this->_name;
    }

    /**
     * Set name
     *
     * @param string $value
     * @return \PhpOffice\PhpSpreadsheet\NamedRange
     */
    public function setName($value = \null) {
    	if ($value !== \NULL) {
    		// Old title
    		$oldTitle = $this->_name;

    		// Re-attach
    		if ($this->_worksheet !== \NULL) {
    			$this->_worksheet->getParent()->removeNamedRange($this->_name,$this->_worksheet);
    		}
    		$this->_name = $value;

    		if ($this->_worksheet !== \NULL) {
    			$this->_worksheet->getParent()->addNamedRange($this);
    		}

	    	// New title
	    	$newTitle = $this->_name;
	    	\PhpOffice\PhpSpreadsheet\ReferenceHelper::getInstance()->updateNamedFormulas($this->_worksheet->getParent(), $oldTitle, $newTitle);
    	}
    	return $this;
    }

    /**
     * Get worksheet
     *
     * @return \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
     */
    public function getWorksheet() {
    	return $this->_worksheet;
    }

    /**
     * Set worksheet
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $value
     * @return \PhpOffice\PhpSpreadsheet\NamedRange
     */
    public function setWorksheet(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $value = \null) {
    	if ($value !== \NULL) {
    		$this->_worksheet = $value;
    	}
    	return $this;
    }

    /**
     * Get range
     *
     * @return string
     */
    public function getRange() {
    	return $this->_range;
    }

    /**
     * Set range
     *
     * @param string $value
     * @return \PhpOffice\PhpSpreadsheet\NamedRange
     */
    public function setRange($value = \null) {
    	if ($value !== \NULL) {
    		$this->_range = $value;
    	}
    	return $this;
    }

    /**
     * Get localOnly
     *
     * @return bool
     */
    public function getLocalOnly() {
    	return $this->_localOnly;
    }

    /**
     * Set localOnly
     *
     * @param bool $value
     * @return \PhpOffice\PhpSpreadsheet\NamedRange
     */
    public function setLocalOnly($value = \false) {
    	$this->_localOnly = $value;
    	$this->_scope = $value ? $this->_worksheet : \null;
    	return $this;
    }

    /**
     * Get scope
     *
     * @return \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet|null
     */
    public function getScope() {
    	return $this->_scope;
    }

    /**
     * Set scope
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet|null $value
     * @return \PhpOffice\PhpSpreadsheet\NamedRange
     */
    public function setScope(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $value = \null) {
    	$this->_scope = $value;
    	$this->_localOnly = ($value == \null) ? \false : \true;
    	return $this;
    }

    /**
     * Resolve a named range to a regular cell range
     *
     * @param string $pNamedRange Named range
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet|null $pSheet Scope. Use null for global scope
     * @return \PhpOffice\PhpSpreadsheet\NamedRange
     */
    public static function resolveRange($pNamedRange = '', \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $pSheet) {
		return $pSheet->getParent()->getNamedRange($pNamedRange, $pSheet);
    }

	/**
	 * Implement PHP __clone to create a deep clone, not just a shallow copy.
	 */
	public function __clone() {
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
