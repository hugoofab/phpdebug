<?php

/**
 * funções úteis para debugar sistemas PHP
 */

// defina suas regras para o modo debug
//define ( 'DEBUG_MODE' , true );
define ( 'DEBUG_MODE' , (isset($_SERVER['HTTP_DEBUG'])&&$_SERVER['HTTP_DEBUG']==="ABCDEFGH") );
define ( 'CONSOLE_MODE' , ( isset ( $_SERVER['PROMPT'] ) ) );

/**
 * dump das variáveis e exit
 * @param mixed todos os parâmetros necessários
 */
function prd ( ) {
    if ( !defined ( 'DEBUG_MODE' ) || DEBUG_MODE === false ) return ;
    $backTrace = debug_backtrace ();
    $varList   = func_get_args ( );

    _pr ( $varList , "#fF0" , "#A00" , $backTrace ) ;

//    $all_vars = get_defined_vars ();
//    _pr ( $all_vars , "#fF0" , "#A00" ) ;

    exit;
}

/**
 * dump das variáveis com fundo vermelho para destacar
 * @param mixed todos os parâmetros necessários
 */
function pre ( ) {
    $backTrace = debug_backtrace ();
    $varList   = func_get_args ( );
    _pr ( $varList , "#ddd" , "#600" ,  $backTrace ) ;
}

/**
 * dump das variáveis com fundo verde para destacar
 * @param mixed todos os parâmetros necessários
 */
function prs ( ) {
    $backTrace = debug_backtrace ();
    $varList   = func_get_args ( );
    _pr ( $varList , "#FFF" , "#005F08" , $backTrace ) ;
}

/**
 * dump das variáveis com fundo preto
 * @param mixed todos os parâmetros necessários
 */
function pr ( ) {
    $varList   = func_get_args ( );
    $backTrace = debug_backtrace ();
    _pr ( $varList , "#0F0" , "#000" , $backTrace );
}

/**
 * dump das variáveis com fundo preto
 * @param mixed todos os parâmetros necessários
 */
function pry ( ) {
    $varList   = func_get_args ( );
    $backTrace = debug_backtrace ();
    _pr ( $varList , "#FF0" , "#000" , $backTrace );
}

/**
 * metodo pra onde será roteado todos os outros PRs
 * @param  array(mixed)  $varList    lista de variáveis para fazer o dump
 * @param  string  $foreground cor para a fonte
 * @param  string  $background cor para o fundo
 * @param  array $backTrace  backtrace, caso queira passar o backtrace de outro método ou deixar que seja criado a partir daqui
 */
function _pr ( $varList = "" , $foreground = "#0F0" , $background = "#000" , $backTrace = false ) {

    $spacePadding = "                                                                                                                                                                                                                                                              ";
    $spacePadding = "";

    if ( !defined ( 'DEBUG_MODE' ) || DEBUG_MODE === false ) return ;
    if ( $backTrace === false ) $backTrace = debug_backtrace ();
    $options = array(
        'File' => $backTrace[0]['file'] ,
        'Line' => $backTrace[0]['line']
    );

    $file = $options['File'];
    $line = $options['Line'];

//    if (CONSOLE_MODE){
//        echo "-- $file : $line --------------------------------------------\n";
//        foreach ( $varList as $var ) {
//            print_r($var);
//            echo "\n";
//        }
//        return;
//    }

    $id = uniqid();// md5 ( print_r ( $varList , true ) . rand ( 0 , 100 ) ) ;
    echo "$spacePadding<pre id=\"$id\" class='hf_debug' style=\"font-size:12px;line-height:1em;background:${background};color:${foreground};position:relative;z-index:99999;filter:alpha(opacity=80); -moz-opacity:0.80; opacity:0.80;font-family:courier new;white-space: pre-wrap;margin:0;margin-bottom:10px;\">" ;
    echo "\n" . $file . ":" . $line . $spacePadding ;
    echo "\n".__getLineContent($file,$line);
    echo "<hr>";
    if ( !empty ( $varList ) ) {
        if (is_array($varList)) {
            $varList = array_reverse($varList);
            foreach ( $varList as $var ) {
                echo _getVarDetails($var);
            }
        }
    }

    array_shift ( $backTrace ) ;
    $backTrace = array_reverse ( $backTrace );

//    if (isset($_GET['BACKTRACE'])){
//        print("<pre>");
//        print_r($backTrace);
//        exit;
//    }
    echo "BackTrace:";
    foreach( $backTrace as $key => $bt ) {
        $outArg = [];
        foreach ( $bt['args'] as &$arg ) {
            if ( gettype ( $arg ) === 'object' ) {
                $objOutArg = "Object:" . get_class($arg) ;
                if ( method_exists($arg,'__toString') ) $objOutArg .= "$arg";
                $outArg[] = $objOutArg ;
            } else if ( gettype ( $arg ) === 'boolean' ) {
                $outArg[] = $arg ? "Bool:TRUE":"Bool:FALSE";
            } else if ( gettype ($arg) === 'array' ) {
                $outArg[] = "Array:" . print_r($arg,true) ;
            } else {
                $outArg[] = ucwords(gettype($arg)).":".$arg ;
            }
        }
        $implode = @implode ( "] , [" , $outArg ) ;
        $function = !empty($bt['function'])? $bt['function'] . "( [" . $implode . "] )" : " - FUNCAO DESCONHECIDA - " ;
        $class = !empty($bt['class'])? $bt['class'] : "";

//        @TODO ESCONDER O CAMINHO COMPLETO DO ARQUIVO MOSTRANDO NO MÁXIMO O ULTIMO DIRETORIO E NOME DO ARQUIVO, E AO CLICAR/PASSAR O MOUSE, MOSTRA COMPLETO
        echo "$spacePadding<span style=\"margin-top:3px;padding-left:4px;background:#070;color:#000;font-weight:bold;\">\n#$key " . $bt['file'] . ":" . $bt['line'] . " </span> - $class-&gt;" . $function ;
    }
    echo "\n$spacePadding<span style=\"margin - bottom:10px;padding - left:4px;background:#0F0;color:#000;font-weight:bold;line-height:1.5em;\"><a style=\"color:#FFF;background:#000;padding-left:5px;\" onclick=\"document.getElementById('$id').innerHTML=''\" href=\"javascript:;\">fechar este &nbsp;&nbsp;</a><a onclick=\"$('.hf_debug').hide()\" style=\"color:#FFF;background:#000;padding-left:5px;\" href=\"javascript:;\">fechar todos</a></span></pre>";
//    echo "                                                                                                                               <span style=\"margin-bottom:10px;padding-left:4px;background:#0F0;color:#000;font-weight:bold;line-height:1.5em;\">\n" . $file . ":" . $line . "                                                                                                                                                                                  &nbsp; <a style=\"color:#FFF;background:#000;padding-left:5px;\" onclick=\"document.getElementById('$id').innerHTML=''\" href=\"javascript:;\">fechar este &nbsp;&nbsp;</a><a onclick=\"$('.hf_debug').hide()\" style=\"color:#FFF;background:#000;padding-left:5px;\" href=\"javascript:;\">fechar todos</a></span></pre>" ;
}

function __getLineContent($file,$line){
    $lines = file($file);//file in to an array
    $output = trim ($lines[$line-1]);
//    $output = preg_replace('/^\s*/',"",$lines[$line-1]); //line 2
//    $output = preg_replace('/\s*$/',"",$output); //line 2
    return $output ;
}

/**
 * retorna detalhes da variável para simplificar o método que faz o dump
 * @param  mixed $mixedVar variável qualquer
 * @return string           detalhes da variável
 */
function _getVarDetails ( $mixedVar ) {
    $output = "Type: " . gettype ( $mixedVar ) . "\n" ;
    if ( gettype ( $mixedVar ) == 'boolean' ) {
        $output .= ( $mixedVar ) ? "TRUE" : "FALSE" ;
    } else {
        $output .= print_r ( $mixedVar , true );
    }
    $output .= "<hr>";
    return $output ;
}

/**
 * ********************************************************************************************************
 * FUNÇÕES EXPERIMENTAIS ABAIXO
 * * ********************************************************************************************************
 */
function translateError ( $errorMessage ) {
    // errorBase.php return a array with key=>value pair where key is the original error and value is translated error
    if ( !file_exists( "errorBase.php" ) ) return $errorMessage ;

    $errorBase = include ( "errorBase.php" );
    if ( !array_key_exists ( $errorMessage , $errorBase ) ) return $errorMessage ;
    $output = $errorBase[$errorMessage];
    if ( DEBUG ) $output .= "<br>[" . $errorMessage . "]" ;
    return $output ;
}

/**
 * Tratamento de erro padrão
 * aqui pode ser definido entre mostrar uma mensagem de erro ou redirecionar para uma página de erro
 * procure logar o erro nesse momento
 * */
function defaultExceptionHandler ( $exception ) {
    $errorData = array (
        'message'   => $exception->getMessage ( ) ,
        'file'      => $exception->getFile ( ) ,
        'line'      => $exception->getLine ( ) ,
        'code'      => $exception->getCode ( )
    ) ;
    Request::getFeedback();
    debugErrorHandler ( "Exception: " . $errorData['code'] , $errorData['message'] , $errorData['file'] , $errorData['line'] ) ;
    // o php morre automaticamente aqui
}

function myErrorHandler ( $errno , $errstr , $errfile , $errline ) {
    $verboseMode = strpos ( $_SERVER['HTTP_USER_AGENT'] , 'DEBUG_MODE_8f40861230f65284d6f2058249344c00_VERBOSE' );
    if ( !$verboseMode ) {
        if ( !( error_reporting ( ) & $errno ) ) {
            // This error code is not included in error_reporting
            return ;
        }
    }
    debugErrorHandler ( "Error: " . $errno , $errstr , $errfile , $errline ) ;
    // acho que deve executar uma ou outra
    Request::addFeedback ( $errstr , 'danger' ) ;
    return true;
}

function debugErrorHandler ( $errno , $errstr , $errfile , $errline ) {

    $backTrace = debug_backtrace ();
    array_shift($backTrace);
    _pr ( ["ERROR: " . $errstr."\n".$errfile.":".$errline] , "#FFF" , "#717100" , $backTrace );
    exit;

    $errstr         = translateError ( $errstr );
    $backTrace      = debug_backtrace ( ) ;

    $bt             = '';
    if ( DEBUG_MODE ) {
        array_shift($backTrace);
        $backTrace = array_reverse ( $backTrace );
        foreach ( $backTrace as $key => $trace ) {
            if ( empty($trace['file']) ) continue ;
            $bt = "[" . ($key+1) . "]" . $trace['file'] . ":" . $trace['line'] . "<br>" . $bt ;
        }
        if ( $bt !== '' ) $bt ='<hr><div style="color:#000;">' . $bt . "</div><br>" ;
    }
    $message =
        '<div style="z-index:9000;position:relative;min-width:500px;background:#FFFAFA;padding:10px;min-height:100px;text-align:center;margin-left:auto;margin-right:auto;text-align:center;color:#F00;font-family:verdana;font-size:12px;margin:10px;">' .
        '<div style="display:block;text-align:left;">' .
        '<div style="font-family:courier new;overflow:auto;border:2px solid #D00;border-radius:10px;font-size:1.3em;color:#D00;padding:10px;"> <span style="font-size:2em;" class="glyphicon glyphicon-warning-sign"></span> ' . $errstr .
        $bt .
        '</div>' .
        //$errfile . ':' . $errline . '<br>' .
        '</div>' .
        '</div>'
    ;
    echo $message ;
}

if (DEBUG_MODE) {

}
set_error_handler ( "myErrorHandler" );
set_exception_handler( 'defaultExceptionHandler' ) ;
