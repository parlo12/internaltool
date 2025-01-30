<?php

namespace Database\Factories;

use App\Models\KnowledgeBase;
use Illuminate\Database\Eloquent\Factories\Factory;

class KnowledgeBaseFactory extends Factory
{
    protected $model = KnowledgeBase::class;

    public function definition()
    {
        $questionsAndAnswers = [
            [
                'question' => 'What is the current value of my home?',
                'answer' => 'The value of your home depends on its location, size, condition, and market trends. We can perform a Comparative Market Analysis to provide a precise estimate.'
            ],
            [
                'question' => 'How long does it typically take to sell a home?',
                'answer' => 'On average, it can take 30-60 days to sell a home, depending on the market and pricing strategy.'
            ],
            [
                'question' => 'Do I need to make repairs before selling?',
                'answer' => 'Making essential repairs can help increase the value of your home and attract buyers. I can advise you on which repairs might be most beneficial.'
            ],
            [
                'question' => 'What are the fees for selling my home?',
                'answer' => 'The typical costs include real estate agent commissions, closing costs, and possible staging or repair expenses. I can provide a detailed breakdown.'
            ],
            [
                'question' => 'Should I stage my home before selling?',
                'answer' => 'Staging your home can help it sell faster and for a better price by making it more appealing to potential buyers.'
            ],
            [
                'question' => 'How do you market homes?',
                'answer' => 'I use online listings, social media, professional photography, open houses, and targeted advertising to reach a wide audience of potential buyers.'
            ],
            [
                'question' => 'Can I sell my home as-is?',
                'answer' => 'Yes, you can sell your home as-is, but it might affect the price. I can help you evaluate the pros and cons based on your situation.'
            ],
            [
                'question' => 'What is the process for selling my home?',
                'answer' => 'The process involves pricing your home, listing it, marketing, showing it to buyers, negotiating offers, and completing the closing process.'
            ],
            [
                'question' => 'Can you help me find a new home after selling mine?',
                'answer' => 'Absolutely! I can assist you in selling your current home and finding a new one that meets your needs.'
            ],
            [
                'question' => 'What happens if my home doesn’t sell?',
                'answer' => 'If your home doesn’t sell within a specific timeframe, we can revisit the pricing strategy or marketing plan to improve its chances.'
            ]
        ];

        $qa = $this->faker->randomElement($questionsAndAnswers);

        return [
            'question' => $qa['question'],
            'answer' => $qa['answer'],
        ];
    }
}
