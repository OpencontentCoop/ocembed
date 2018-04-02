<?php

class OCEmbedOperator
{
    /*!
      Constructor, does nothing by default.
    */
    
    public static $filters = array();
    
    function __construct()
    {
        $this->Operators= array( 'autoembed', 'get_oembed_object', 'search_embed' );
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
        return array(
            'autoembed' => array
            (
                'separator' => array
                (
                    'type' => 'mixed',
                    'required' => false,
                    'default' => array( '<div class="text-center">', '</div>' )
                ),
                'parameters' => array
                (
                    'type' => 'array',
                    'required' => false,
                    'default' => array()
                )                 
            ),
            'search_embed' => array
            (
                'separator' => array
                (
                    'type' => 'string',
                    'required' => false,
                    'default' => '<br/>'
                ),
                'parameters' => array
                (
                    'type' => 'array',
                    'required' => false,
                    'default' => array()
                )                 
            ),
            'get_oembed_object' => array
            (
                'url' => array
                (
                    'type' => 'string',
                    'required' => true                  
                ),
                'parameters' => array
                (
                    'type' => 'array',
                    'required' => false,
                    'default' => array()
                )                 
            )
        );
    }
    
    function modify( &$tpl, &$operatorName, &$operatorParameters, &$rootNamespace, &$currentNamespace, &$operatorValue, &$namedParameters )
    {
		
        switch ( $operatorName )
        {

            case 'autoembed':
            {                
                $operatorValue = $this->embed->autoembed($operatorValue, $namedParameters['separator'], $namedParameters['parameters']);
            }break;
            
            case 'search_embed':
            {                
                $operatorValue = $this->embed->search_embed($operatorValue, $namedParameters['separator'], $namedParameters['parameters']);
            }break;
            
            case 'get_oembed_object':
            {                
                $operatorValue = $this->oembed->get_html( $namedParameters['url'], $namedParameters['parameters'], true );
            }break;            
            
        }
    }    
        
}

?>
