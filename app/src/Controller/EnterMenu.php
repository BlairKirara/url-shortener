<?php

namespace App\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EnterMenu
{

#[Route('/enter', name: 'enter_index', methods: 'GET')]
public function index(Request $request): Response
{
    $name = $request->query->getAlnum('name', 'World');

    return new Response('Hello '.$name.'!');
}
}