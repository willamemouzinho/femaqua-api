<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="FEMAQUA API",
 *      description="Documentação da API FEMAQUA para gerenciamento de repositório de ferramentas. A API oferece funcionalidades de criação, visualização, edição e exclusão de ferramentas, além de autenticação de usuários via tokens.",
 *      @OA\Contact(
 *          email="willame.dev@gmail.com"
 *      )
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="API Token",
 *     description="Autenticação via token Bearer. Insira o token no formato 'Bearer {token}'."
 * )
 */
abstract class Controller
{
    //
}
