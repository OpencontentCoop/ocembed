<?php
require 'autoload.php';

$script = eZScript::instance( array( 'description' => ( "Clear ocembed cached data" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions();
$script->initialize();
$script->setUseDebugAccumulators( true );

try
{
    eZCLI::instance()->output( "Clear OCEmbed db cache" );
    OCEmbed::clearCachedData();

    $script->shutdown();
}
catch( Exception $e )
{
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown( $errCode, $e->getMessage() );
}
