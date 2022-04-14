<?php

use Fiks\MureDiscord\Route;

/**
 *  Команды, которые реагируют на пришедшие сообщения
 */
Route::namespaces('Fiks\\MureDiscord\\Commands\\MessageCreate', function() {
    Route::addCommand('register', 'RegisterCommand');
    Route::addCommand('ping', 'PingCommand');
});

/**
 * Команды, которые выполняются в случае удаления сообщений
 */
Route::namespaces('Fiks\\MureDiscord\\Commands\\MessageDelete', function() {

});

/**
 * Команды, которые реагируют в случае редактирования сообщения
 */
Route::namespaces('Fiks\\MureDiscord\\Commands\\MessageUpdate', function() {

});

/**
 * Команды, которые реагируют в случае добавления какой-то реакции на сообщения
 */
Route::namespaces('Fiks\\MureDiscord\\Commands\\MessageReactionAdd', function() {

});

/**
 * Команды, которые реагируют в случае удаления какой-то реакции
 */
Route::namespaces('Fiks\\MureDiscord\\Commands\\MessageReactionRemove', function() {

});

/**
 * Команды, которые реагируют в случае удаления всех реакций
 */
Route::namespaces('Fiks\\MureDiscord\\Commands\\MessageReactionDeleteAll', function() {

});


