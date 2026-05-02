<?php

declare(strict_types=1);

return [
    'webhook' => [
        'invalid_signature' => 'Assinatura de webhook InvoiceXpress inválida.',
        'received' => 'Webhook recebido.',
    ],
    'errors' => [
        'auth_failed' => 'Autenticação falhou para a conta InvoiceXpress ":account".',
        'not_found' => 'Recurso :resource não encontrado no InvoiceXpress.',
        'rate_limit' => 'Limite de pedidos InvoiceXpress excedido — tente novamente em :seconds segundos.',
        'validation_failed' => 'Erro de validação InvoiceXpress nos campos: :fields.',
        'server_error' => 'Erro no servidor InvoiceXpress (HTTP :status).',
    ],
];
