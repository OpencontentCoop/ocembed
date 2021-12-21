<?php

class OCEmbedOperator
{
    /*!
      Constructor, does nothing by default.
    */

    public static $filters = [];

    function __construct()
    {
        $this->Operators = ['autoembed', 'get_oembed_object', 'search_embed'];
        $this->embed = new OCEmbed;
        $this->oembed = new OCoEmbed;
    }

    /*!
    Return an array with the template operator name.
    */
    function operatorList()
    {
        return $this->Operators;
    }

    /*!
     \return true to tell the template engine that the parameter list exists per operator type,
             this is needed for operator classes that have multiple operators.
    */
    function namedParameterPerOperator()
    {
        return true;
    }

    /*!
     See eZTemplateOperator::namedParameterList
    */
    function namedParameterList()
    {
        return [
            'autoembed' => [
                'separator' => [
                    'type' => 'mixed',
                    'required' => false,
                    'default' => ['<div class="text-center">', '</div>'],
                ],
                'parameters' => [
                    'type' => 'array',
                    'required' => false,
                    'default' => [],
                ],
            ],
            'search_embed' => [
                'separator' => [
                    'type' => 'string',
                    'required' => false,
                    'default' => '<br/>',
                ],
                'parameters' => [
                    'type' => 'array',
                    'required' => false,
                    'default' => [],
                ],
            ],
            'get_oembed_object' => [
                'url' => [
                    'type' => 'string',
                    'required' => true,
                ],
                'parameters' => [
                    'type' => 'array',
                    'required' => false,
                    'default' => [],
                ],
                'separator' => [
                    'type' => 'string',
                    'required' => false,
                    'default' => false,
                ],
            ],
        ];
    }

    function modify(&$tpl, &$operatorName, &$operatorParameters, &$rootNamespace, &$currentNamespace, &$operatorValue, &$namedParameters)
    {

        switch ($operatorName) {

            case 'autoembed':
                {
                    $operatorValue = $this->embed->autoembed($operatorValue, $namedParameters['separator'], $namedParameters['parameters']);
                }
                break;

            case 'search_embed':
                {
                    $operatorValue = $this->embed->search_embed($operatorValue, $namedParameters['separator'], $namedParameters['parameters']);
                }
                break;

            case 'get_oembed_object':
                {
                    $operatorValue = (array)$this->oembed->get_html($namedParameters['url'], $namedParameters['parameters'], true, true, $namedParameters['separator']);
                }
                break;

        }
    }

}

?>
