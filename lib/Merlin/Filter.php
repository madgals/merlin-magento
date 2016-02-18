<?php

namespace Merlin;

class Filter extends MultiFieldParameter {

    protected $exp;
    protected $tag;
    protected $type;
    

    const TYPE_CNF = 'cnf';
    const TYPE_DNF = 'dnf';

    public function __construct($field, $operator, $value, $tag = null, $type = null) {
        // TODO escape
        $this->exp = Filter::makeExpression($field, $operator, $value);

        $this->tag = $tag;
        $this->type = $type;

    }

    static function makeExpression($field, $operator, $value) {
        $arr = array(
            "!" => '\!',
            "," => "\\,",
            "|" => "\\|",
            "\\" => "\\\\"
            ); 
        $value = strtr($value,$arr);

        $rhs = "";
        switch ($operator) {
            case '=':
                $rhs = $value;
                break;
            case '!=':
                $rhs = "!" . $value;
                break;
            case '<':
                $rhs = '(:' . $value . ')';
                break;
            case '>':
                $rhs = '(' . $value . ':)';
                break;
            case '<=':
                $rhs = '(:' . $value . ']';
                break;
            case '>=':
                $rhs = '[' . $value . ':)';
                break;
            default:
                throw new MerlinException('Filter expression operator must be one of the following: =, !=, <, >, <=, or >=.');
        }

        return "$field:$rhs";
    }

    public function addAnd($field, $operator, $value) {
        $this->exp .= ',' . Filter::makeExpression($field, $operator, $value);
        return $this;
    }

    public function addOr($field, $operator, $value) {
        $this->exp .= '|' . Filter::makeExpression($field, $operator, $value);
        return $this;
    }

}
