<?php

/**
 * Form field to display an editable date string
 * in three separate dropdown fields for day, month and year.
 * 
 * # Configuration
 * 
 * - 'range': set the range of the Year Drowpdown
 * - 'dateformat' (string): Date format compatible with Zend_Date.
 *    Usually set to default format for {@link locale} through {@link Zend_Locale_Format::getDateFormat()}.
 * - 'dmyseparator' (string): HTML markup to separate day, month and year fields.
 *    Only applicable with 'dmyfields'=TRUE. Use 'dateformat' to influence date representation with 'dmyfields'=FALSE.
 * - 'datavalueformat' (string): Internal ISO format string used by {@link dataValue()} to save the
 *    date to a database.
 * - 'min' (string): Minimum allowed date value (in ISO format, or strtotime() compatible).
 *    Example: '2010-03-31', or '-7 days'
 * - 'max' (string): Maximum allowed date value (in ISO format, or strtotime() compatible).
 *    Example: '2010-03-31', or '1 year'
 * 
 * # Localization
 * 
 * The field will get its default locale from {@link i18n::get_locale()}, and set the `dateformat`
 * configuration accordingly. Changing the locale through {@link setLocale()} will not update the 
 * `dateformat` configuration automatically.
 * 
 * See http://doc.silverstripe.org/framework/en/topics/i18n for more information about localizing form fields.
 * 
 * # Usage
 * 
 * ## Example: German dates with separate fields for day, month, year
 * 
 *   $f = new DropdownDateField('MyDate');
 *   $f->setLocale('de_DE');
 * 
 * # Validation
 * 
 * Caution: JavaScript validation is only supported for the 'en_NZ' locale at the moment,
 * it will be disabled automatically for all other locales.
 * 
 * @package forms
 * @subpackage fields-datetime
 */
class DropdownDateField extends DateField
{
    
    /**
     * @var array
     */
    private static $default_config = array(
        'showcalendar' => false,
        'dateformat' => null,
        'datavalueformat' => 'yyyy-MM-dd',
        'dmyseparator' => '',
        'range' => null,
        'min' => null,
        'max' => null,
    );
    
    /**
     * @var array
     */
    protected $config;
        
    /**
     * @var String
     */
    protected $locale = null;
    
    /**
     * @var Zend_Date Just set if the date is valid.
     * {@link $value} will always be set to aid validation,
     * and might contain invalid values.
     */
    protected $valueObj = null;
    
    public function __construct($name, $title = null, $value = null)
    {
        Config::inst()->update('DateField', 'default_config', self::$default_config);

        parent::__construct($name, $title, $value);
    }

    public function FieldHolder($properties = array())
    {
        $html = parent::FieldHolder();
        return $html;
    }
    
    public function SmallFieldHolder($properties = array())
    {
        $html = parent::SmallFieldHolder($properties);
        return $html;
    }

    public function Field($properties = array())
    {
        $config = array(
            'isoDateformat' => $this->getConfig('dateformat'),
            'min' => $this->getConfig('min'),
            'max' => $this->getConfig('max')
        );
        $config = array_filter($config);
        foreach ($config as $k => $v) {
            $this->setAttribute('data-' . $k, $v);
        }
        
        // values
        $valArr = ($this->valueObj) ? $this->valueObj->toArray() : null;

        // fields
        $fieldNames = Zend_Locale::getTranslationList('Field', $this->locale);
        
        $dropdownDays = array(
            'NotSet' => $fieldNames['day'],
            '01'=>'01', '02'=>'02', '03'=>'03', '04'=>'04', '05'=>'05',
            '06'=>'06', '07'=>'07', '08'=>'08', '09'=>'09', '10'=>'10',
            '11'=>'11', '12'=>'12', '13'=>'13', '14'=>'14', '15'=>'15',
            '16'=>'16', '17'=>'17', '18'=>'18', '19'=>'19', '20'=>'20',
            '21'=>'21', '22'=>'22', '23'=>'23', '24'=>'24', '25'=>'25',
            '26'=>'26', '27'=>'27', '28'=>'28', '29'=>'29', '30'=>'30',
            '31'=>'31'
        );
        
        $fieldDay = DropdownField::create($this->name . '[day]', false, $dropdownDays, ($valArr) ? $valArr['day'] : null)->addExtraClass('day '.$this->extraClass());
        
        $dropdownMonths = array(
            'NotSet' => $fieldNames['month'],
            '01'=>'01', '02'=>'02', '03'=>'03', '04'=>'04', '05'=>'05',
            '06'=>'06', '07'=>'07', '08'=>'08', '09'=>'09', '10'=>'10',
            '11'=>'11', '12'=>'12'
        );
        
        $fieldMonth = DropdownField::create($this->name . '[month]', false, $dropdownMonths, ($valArr) ? $valArr['month'] : null)->addExtraClass('month '.$this->extraClass());
        
        $fieldYear = DropdownField::create($this->name . '[year]', false, $this->dropdownYears($fieldNames['year']), ($valArr) ? $valArr['year'] : null)->addExtraClass('year '.$this->extraClass());
        
        $sep = $this->getConfig('dmyseparator');
        $format = $this->getConfig('dateformat');
        $fields = array();
        $fields[stripos($format, 'd')] = $fieldDay->Field();
        $fields[stripos($format, 'm')] = $fieldMonth->Field();
        $fields[stripos($format, 'y')] = $fieldYear->Field();
        ksort($fields);
        $html = implode($sep, $fields);
        
        return $html;
    }
    
    protected function dropdownYears($title)
    {
        if ($range = $this->getConfig('range')) {
            list($from, $to) = explode('-', $range);
        } else {
            if ($min = $this->getConfig('min')) {
                if (Zend_Date::isDate($min, $this->getConfig('datavalueformat'))) {
                    $minDate = new Zend_Date($min, $this->getConfig('datavalueformat'));
                } else {
                    $minDate = new Zend_Date(strftime('%Y-%m-%d', strtotime($min)), $this->getConfig('datavalueformat'));
                }
                $from = $minDate->toString('Y');
            } else {
                $from = (Zend_Date::now()->toString('Y') - 99);
            }
            if ($max = $this->getConfig('max')) {
                if (Zend_Date::isDate($max, $this->getConfig('datavalueformat'))) {
                    $maxDate = new Zend_Date($max, $this->getConfig('datavalueformat'));
                } else {
                    $maxDate = new Zend_Date(strftime('%Y-%m-%d', strtotime($max)), $this->getConfig('datavalueformat'));
                }
                $to = $maxDate->toString('Y');
            } else {
                $to = (Zend_Date::now()->toString('Y') + 99);
            }
        }
        $dropdownData = array();
        $dropdownData['NotSet'] = $title;
        for ($i = $to; $i >= $from; $i--) {
            $dropdownData[$i]=$i;
        }
        return $dropdownData;
    }
            

    public function Type()
    {
        return 'date text';
    }
        
    /**
     * Sets the internal value to ISO date format.
     * 
     * @param String|Array $val 
     */
    public function setValue($val)
    {
        if (empty($val)) {
            $this->value = null;
            $this->valueObj = null;
        } else {
            // Setting in correct locale
            if (is_array($val) && $this->validateArrayValue($val)) {
                // set() gets confused with custom date formats when using array notation
                if (!(empty($val['day']) || empty($val['month']) || empty($val['year']))) {
                    $this->valueObj = new Zend_Date($val, null, $this->locale);
                    $this->value = $this->valueObj->toArray();
                } else {
                    $this->value = $val;
                    $this->valueObj = null;
                }
            } elseif (!empty($val) && Zend_Date::isDate($val, $this->getConfig('datavalueformat'), $this->locale)) {
                // load ISO date from database (usually through Form->loadDataForm())
                $this->valueObj = new Zend_Date($val, $this->getConfig('datavalueformat'), $this->locale);
                $this->value = $this->valueObj->toArray();
            } else {
                $this->value = $val;
                $this->valueObj = null;
            }
        }

        return $this;
    }
    
    public function performReadonlyTransformation()
    {
        $field = $this->castedCopy('DateField_Disabled');
        $field->setValue($this->dataValue());
        $field->readonly = true;
        
        return $field;
    }

    /**
     * @return Boolean
     */
    public function validate($validator)
    {
        $valid = true;
        
        // Don't validate empty fields
        if (empty($this->value)) {
            return true;
        }

        $valid = (!$this->value || $this->validateArrayValue($this->value));
        
        if (!$valid) {
            $validator->validationError(
                $this->name,
                _t(
                    'DateField.VALIDDATEFORMAT2', "Please enter a valid date format ({format})",
                    array('format' => $this->getConfig('dateformat'))
                ),
                "validation",
                false
            );
            return false;
        }
        
        // min/max - Assumes that the date value was valid in the first place
        if ($min = $this->getConfig('min')) {
            // ISO or strtotime()
            if (Zend_Date::isDate($min, $this->getConfig('datavalueformat'))) {
                $minDate = new Zend_Date($min, $this->getConfig('datavalueformat'));
            } else {
                $minDate = new Zend_Date(strftime('%Y-%m-%d', strtotime($min)), $this->getConfig('datavalueformat'));
            }
            if (!$this->valueObj->isLater($minDate) && !$this->valueObj->equals($minDate)) {
                $validator->validationError(
                    $this->name,
                    _t(
                        'DateField.VALIDDATEMINDATE',
                        "Your date has to be newer or matching the minimum allowed date ({date})",
                        array('date' => $minDate->toString($this->getConfig('dateformat')))
                    ),
                    "validation",
                    false
                );
                return false;
            }
        }
        if ($max = $this->getConfig('max')) {
            // ISO or strtotime()
            if (Zend_Date::isDate($min, $this->getConfig('datavalueformat'))) {
                $maxDate = new Zend_Date($max, $this->getConfig('datavalueformat'));
            } else {
                $maxDate = new Zend_Date(strftime('%Y-%m-%d', strtotime($max)), $this->getConfig('datavalueformat'));
            }
            if (!$this->valueObj->isEarlier($maxDate) && !$this->valueObj->equals($maxDate)) {
                $validator->validationError(
                    $this->name,
                    _t('DateField.VALIDDATEMAXDATE',
                        "Your date has to be older or matching the maximum allowed date ({date})",
                        array('date' => $maxDate->toString($this->getConfig('dateformat')))
                    ),
                    "validation",
                    false
                );
                return false;
            }
        }
        
        return true;
    }
}
