<?php

namespace App\Services;

class DynamicTagsService
{
    protected $api_key;

    public function __construct($api_key = '')
    {
        $this->api_key = $api_key;
    }
    public function composeMessage($contact, $messageTemplate)
    {
        $message = $this->replace_placeholders($messageTemplate, $contact);
        return  $message;
    }

    public function spintax($text)
    {
        return preg_replace_callback(
            '/\{(((?>[^\{\}]+)|(?R))*)\}/x',
            function ($text) {
                $text = $text[1];
                $parts = explode('|', $text);
                return $parts[array_rand($parts)];
            },
            $text
        );
    }
    public function replace_placeholders($template, $contact)
    {
        $standardFields = [
            'phone',
            'contact_name',
            'email',
            'address',
            'zipcode',
            'city',
            'state',
            'age',
            'gender',
            'lead_score',
            'agent',
            'novation',
            'creative_price',
            'monthly',
            'downpayment',
            'offer'
        ];
    
        $placeholders = [];
    
        // Build placeholder map from standard fields
        foreach ($standardFields as $field) {
            $placeholder = '{{' . $field . '}}';
            $placeholders[$placeholder] = $contact->$field ?? '';
        }
    
        // Use preg_replace for case-insensitive replacement
        foreach ($placeholders as $placeholder => $value) {
            $pattern = '/' . preg_quote($placeholder, '/') . '/i'; // 'i' modifier for case-insensitive
            $template = preg_replace($pattern, $value, $template);
        }
    
        // Remove any remaining placeholders
        return preg_replace('/{{\w+}}/i', '', $template);
    }
    public function get_placeholders($group_id)
    {
        $CRMAPIRequestsService = new CRMAPIRequestsService($this->api_key);
        $contact = $CRMAPIRequestsService->getFirstContact($group_id);
        $contact_info = $CRMAPIRequestsService->get_contact($contact['uid'], $group_id);
        $placeholders = $this->create_placeholders($contact_info);
        $placeholderKeys = array_keys($placeholders);
        return $placeholderKeys;
    }
    private function create_placeholders($contact)
    {
        // Define key mappings from encountered keys to placeholder keys
        $keyMappings = [
            'PHONE'          => 'phone',
            'ADDRESS'        => 'address',
            'CITY'           => 'city',
            'STATE'          => 'state',
            'ZIPCODE'       => 'zipcode',
            'OFFER_AMOUNT'   => 'offer',
            'SALES_PERSON'   => 'agent',
            'AGE'            => 'age',
            'GENDER'         => 'gender',
            'LEAD_SCORE'     => 'lead_score',
            'EMAIL'          => 'email',
            'NOVATION'       => 'novation',
            'CREATIVEPRICE'  => 'creative_price',
            'MONTHLY'        => 'monthly',
            'DOWNPAYMENT'    => 'downpayment',
        ];

        // Initialize placeholders with direct values from contact
        $placeholders = [
            '{{phone}}' => $contact['phone'] ?? '',
        ];

        // Handle first/last name combination
        $firstName = $contact['custom_fields']['FIRST_NAME'] ?? '';
        $lastName = $contact['custom_fields']['LAST_NAME'] ?? '';
        $contactName = trim("$firstName $lastName");
        if (!empty($contactName)) {
            $placeholders['{{contact_name}}'] = $contactName;
        }

        // Process custom fields with case-insensitive matching
        foreach ($contact['custom_fields'] as $key => $value) {
            $upperKey = strtoupper($key);

            if (isset($keyMappings[$upperKey])) {
                // Handle mapped fields
                $mappedKey = $keyMappings[$upperKey];
                $placeholder = '{{' . $mappedKey . '}}';

                // Don't overwrite contact_name if already set from first/last name
                if ($mappedKey === 'contact_name' && isset($placeholders[$placeholder])) {
                    continue;
                }

                $placeholders[$placeholder] = $value;
            } else {
            }
        }

        return $placeholders;
    }
}
