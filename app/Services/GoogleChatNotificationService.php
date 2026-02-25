<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GoogleChatNotificationService
{
    protected string $webhookUrl;

    public function __construct()
    {
        $this->webhookUrl = env('GOOGLE_CHAT_WEBHOOK_URL', '');

        if (empty($this->webhookUrl)) {
            Log::error('Google Chat Webhook URL não está configurada. Defina GOOGLE_CHAT_WEBHOOK_URL no .env');
        }
    }

    /**
     * Envia uma notificação de novo termo de pesquisa para o Google Chat.
     *
     * @param string $searchTermText O texto do termo de pesquisa.
     * @param string $campaignName O nome da campanha.
     * @param string $adGroupName O nome do grupo de anúncios.
     * @param string $keywordText O texto da palavra-chave associada.
     * @param mixed $searchTermId O ID do termo de pesquisa (para URLs).
     * @param mixed $campaignId O ID da campanha (para referência futura, não usado na URL atual).
     * @param mixed|null $adGroupId O ID do grupo de anúncios (opcional, para referência futura).
     */
    public function sendNewSearchTermNotification(
        string $searchTermText,
        string $campaignName,
        string $adGroupName,
        string $keywordText,
        mixed $searchTermId, // Mantido para ID único do card, não usado na URL atual
        mixed $campaignId,
        mixed $adGroupId = null
    ): void {
        if (empty($this->webhookUrl)) {
            Log::error('Tentativa de enviar notificação do Google Chat sem URL de webhook configurada.');
            return;
        }

        // Payload CardV2 Mínimo (Apenas Header)
        $payload = [
            'cardsV2' => [
                [
                    'cardId' => 'minCard-' . $searchTermId . '-' . time(),
                    'card' => [
                        'header' => [
                            'title' => $searchTermText,
                            'imageUrl' => 'https://img.icons8.com/fluency/48/google-ads.png', // Manter ícone
                            'imageType' => 'CIRCLE',
                        ],
                        'sections' => [ // Reintroduzindo sections
                            [
                                'widgets' => [
                                    [ // Widget para Campanha
                                        'decoratedText' => [
                                            'topLabel' => 'Campanha',
                                            'text' => $campaignName,
                                            'wrapText' => true
                                        ]
                                    ],
                                    [ // Widget para Grupo de Anúncios
                                        'decoratedText' => [
                                            'topLabel' => 'Grupo de Anúncios',
                                            'text' => $adGroupName,
                                            'wrapText' => true
                                        ]
                                    ],
                                    [ // Widget para Palavra-chave
                                        'decoratedText' => [
                                            'topLabel' => 'Palavra-chave',
                                            'text' => $keywordText,
                                            'wrapText' => true
                                        ]
                                    ]
                                    // Botões em seção separada
                                ]
                            ],
                            [ // Nova seção apenas para os botões (agora com texto em vez de ícones)
                                'widgets' => [
                                    [
                                        'buttonList' => [
                                            'buttons' => [
                                                [ // Botão Adicionar Keyword (azul)
                                                    'text' => 'Add. Kw.',
                                                    'onClick' => [
                                                        // Gerar URL para a rota 'keyword.add.create' com os parâmetros
                                                        'openLink' => ['url' => route('keyword.add.create', [
                                                            'term' => $searchTermText,
                                                            'ad_group_id' => $adGroupId, // Passar o ID do Ad Group
                                                            'ad_group_name' => $adGroupName, // Passar o nome do Ad Group
                                                        ])]
                                                    ],
                                                    'color' => [ // Cor Azul
                                                        'red' => 0.1,
                                                        'green' => 0.4,
                                                        'blue' => 0.9,
                                                        'alpha' => 1
                                                    ]
                                                ],
                                                [ // Botão Negativar (vermelho) - Mantido
                                                    'text' => 'Neg.',
                                                    'onClick' => [
                                                        // Gerar URL para a rota 'negative-keyword.create' com os parâmetros
                                                        'openLink' => ['url' => route('negative-keyword.create', [
                                                            'term' => $searchTermText,
                                                            'campaign_name' => $campaignName,
                                                            'ad_group_name' => $adGroupName,
                                                            'keyword_text' => $keywordText,
                                                            // 'match_type' pode ser adicionado aqui se quisermos pré-definir pela notificação
                                                        ])]
                                                    ],
                                                    'color' => [
                                                        'red' => 0.8,
                                                        'green' => 0.2,
                                                        'blue' => 0.2,
                                                        'alpha' => 1
                                                    ]
                                                ],
                                            ],
                                        ],
                                    ]
                                ]
                            ]
                        ]
                    ],
                ],
            ],
        ];

        // Adiciona log detalhado do payload antes do envio
        Log::debug('Google Chat CardV2 Payload (Header + Campaign Section):', ['payload' => $payload]);

        try {
            $response = Http::post($this->webhookUrl, $payload);

            if ($response->failed()) {
                Log::error('Falha ao enviar notificação para o Google Chat.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'payload' => $payload, // Logar o payload pode ajudar a debugar
                ]);
            } else {
                 // Logar a resposta mesmo em caso de sucesso aparente
                 Log::info('Notificação enviada para o Google Chat (verificar se chegou).', [
                    'term' => $searchTermText,
                    'response_status' => $response->status(),
                    'response_body' => $response->body(), // Loga o corpo da resposta
                 ]);
            }
        } catch (Throwable $e) {
            Log::error('Erro EXCEPCIONAL ao enviar requisição HTTP para o Google Chat.', [ // Adicionado EXCEPCIONAL para diferenciar
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                 'payload' => $payload,
            ]);
        }
    }
}
