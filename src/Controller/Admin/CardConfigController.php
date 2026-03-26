<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CardConfigController extends AbstractController{
    #[Route('/admin/card/config', name: 'app_admin_card_config')]
    public function index(): Response
    {
        $cards = [];
        $file = fopen("./../cards.txt", "r");

        while (($line = fgets($file)) !== false) {
            $cards[] = trim($line);
        }

        fclose($file);

        return $this->render('admin/card_config/index.html.twig', [
            'controller_name' => 'Admin/CardConfigController',
            'cards' => $cards,
        ]);
    }
}
