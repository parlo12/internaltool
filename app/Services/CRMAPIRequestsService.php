<?php

namespace App\Services;

use GuzzleHttp\Client;

class CRMAPIRequestsService
{
    protected $api_key;

    public function __construct($api_key = '')
    {
        $this->api_key = $api_key;
    }
    public function get_group_name($group_id,)
    {
        $client = new Client();
        $url = 'https://godspeedoffers.com/api/v3/contacts/' . $group_id . '/show';
        $token = $this->api_key;

        try {
            $response = $client->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
            ]);

            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();

            if ($statusCode == 200) {
                $data = json_decode($body, true);
                if (isset($data['data']['name'])) {
                    return $data['data']['name'];
                } else {
                    return null;
                }
            } else {
                return null;
            }
        } catch (\Exception $e) {
            return null;
        }
    }
    public function get_all_contacts($group_id)
    {
        $client = new Client();
        $url = 'https://godspeedoffers.com/api/v3/contacts/' . $group_id . '/all';
        $token = $this->api_key;
        $allContacts = [];
        $currentPage = 1;
        $totalPages = 1;
        do {
            $response = $client->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                ],
                'query' => [
                    'page' => $currentPage,
                ],
            ]);
            $data = json_decode($response->getBody()->getContents(), true);
            if ($data['status'] == 'success') {
                $allContacts = array_merge($allContacts, $data['data']['data']);
                $currentPage++;
                $totalPages = $data['data']['last_page'];
            } else {
                break;
            }
        } while ($currentPage <= $totalPages);
        $contacts = array_map(function ($contact) {
            return [
                'uid' => $contact['uid'],
                'phone' => $contact['phone'],
            ];
        }, $allContacts);
        return $contacts;
    }
    public function get_contact_groups()
    {
        $url = 'https://www.godspeedoffers.com/api/v3/contacts';
        $token = $this->api_key;
        $client = new Client();
        try {
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ],
            ]);
            $body = $response->getBody();
            $data = json_decode($body, true);
            if ($data['status'] == 'success') {
                return $data['data'];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Failed to retrieve contacts'
                ];
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function get_contact($contact_uid, $group_id)
    {
        $url = "https://www.godspeedoffers.com/api/v3/contacts/{$group_id}/search/{$contact_uid}";
        $token = $this->api_key;
        $client = new Client();
        $response = $client->request('POST', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ],
        ]);
        $data = json_decode($response->getBody(), true);
        if ($data['status'] == 'success') {
            return $data['data'];
        } else {
            throw new \Exception('Failed to retrieve contact');
        }
    }
    public function getFirstContact($group_id)
    {
        $client = new Client();
        $url = 'https://godspeedoffers.com/api/v3/contacts/' . $group_id . '/all';
        $token = $this->api_key;
        $currentPage = 1;
        $totalPages = 1;
        do {
            $response = $client->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                ],
                'query' => [
                    'page' => $currentPage,
                ],
            ]);
            $data = json_decode($response->getBody()->getContents(), true);
            if ($data['status'] == 'success') {
                $contacts = $data['data']['data'];
                if (!empty($contacts)) {
                    $firstContact = [
                        'uid' => $contacts[0]['uid'],
                        'phone' => $contacts[0]['phone'],
                    ];
                    return $firstContact;
                }
                $currentPage++;
                $totalPages = $data['data']['last_page'];
            } else {
                break;
            }
        } while ($currentPage <= $totalPages);
        return null;
    }

    public function group_has_contacts($group_id)
    {
        $client = new Client();
        $url = 'https://godspeedoffers.com/api/v3/contacts/' . $group_id . '/all';
        $token = $this->api_key;
        $currentPage = 1;
        $totalPages = 1;

        do {
            $response = $client->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                ],
                'query' => [
                    'page' => $currentPage,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if ($data['status'] == 'success') {
                $contacts = $data['data']['data'];
                if (!empty($contacts)) {
                    return true; // The group has contacts
                }
                $currentPage++;
                $totalPages = $data['data']['last_page'];
            } else {
                // Handle the error as per your application's requirement
                break;
            }
        } while ($currentPage <= $totalPages);

        return false; // The group has no contacts
    }
}
