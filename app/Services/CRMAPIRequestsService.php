<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CRMAPIRequestsService
{
    protected $api_key;

    public function __construct($api_key = '')
    {
        $this->api_key = $api_key;
    }
    /**
     * Get the name of a contact group from the CRM by group ID.
     *
     * @param string|int $group_id The CRM group identifier.
     * @return string|null Returns the group name on success, or null on failure.
     *
     * Error modes: returns null if the HTTP request fails, if the API responds with a non-200
     * status code, or if the response payload doesn't contain the expected name field.
     */
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
        /**
         * Retrieve all contacts from a CRM group, handling pagination.
         *
         * @param string|int $group_id The CRM group identifier.
         * @return array Returns an array of contacts with keys 'uid' and 'phone'.
         *
         * Error modes: If any request fails or returns unexpected structure, the function
         * will stop and return the contacts collected so far (possibly empty).
         */
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
        /**
         * Fetch list of contact groups from the CRM.
         *
         * @return array|\Illuminate\Http\JsonResponse Returns an array of groups on success,
         * or a JSON error response on exception.
         *
         * Note: This returns the raw data structure from the CRM on success. On failure it
         * returns a JSON response with status and message for controller-level handling.
         */
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
        /**
         * Retrieve a single contact by UID within a group from the CRM.
         *
         * @param string $contact_uid The contact's UID in CRM.
         * @param string|int $group_id The CRM group identifier.
         * @return array Returns contact data array on success.
         * @throws \Exception Throws if the CRM responds with an error status.
         */
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
        /**
         * Return the first contact (uid and phone) from the specified group.
         *
         * @param string|int $group_id The CRM group identifier.
         * @return array|null An array with keys 'uid' and 'phone' or null if none found.
         *
         * Error modes: If pagination or API fails, returns null.
         */
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
        /**
         * Check whether a CRM group contains any contacts.
         *
         * @param string|int $group_id The CRM group identifier.
         * @return bool True if at least one contact exists in the group, false otherwise.
         */
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

    public function createGroup($name)
    {
        /**
         * Create a new contact group in the CRM.
         *
         * @param string $name The desired group name.
         * @return \Illuminate\Http\JsonResponse JSON response describing success or failure.
         */
        $response = Http::withToken($this->api_key)
            ->acceptJson()
            ->post('https://www.godspeedoffers.com/api/v3/contacts', [
                'name' => $name,
            ]);

        if ($response->successful()) {
            return response()->json([
                'status' => 'success',
                'data' => $response->json(),
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => $response->json('message') ?? 'Failed to create group.',
            ], $response->status());
        }
    }


public function createContact(string $groupUid, array $contactData)
{
    /**
     * Create a contact inside a specific CRM group.
     *
     * @param string $groupUid The CRM group UID where the contact will be created.
     * @param array $contactData Associative array of contact fields to send to CRM.
     * @return \Illuminate\Http\JsonResponse JSON response with created contact data or error message.
     *
     * Notes: Logs the CRM response on failure for debugging.
     */
    $token = $this->api_key; // Replace with your token

    $response = Http::withToken($token)
        ->acceptJson()
        ->post("https://www.godspeedoffers.com/api/v3/contacts/{$groupUid}/store", $contactData);

    if ($response->successful()) {
        return response()->json([
            'status' => 'success',
            'data' => $response->json('data'),
        ]);
    } else {
        Log::error('Failed to create contact', [
            'response' => $response->json(),
            'status_code' => $response->status(),
        ]);
        return response()->json([
            'status' => 'error',
            'message' => $response->json('message') ?? 'Failed to create contact.',
        ], $response->status());
    }
}

}
