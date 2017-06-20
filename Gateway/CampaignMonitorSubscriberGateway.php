<?php

namespace MailMotor\Bundle\CampaignMonitorBundle\Gateway;

use MailMotor\Bundle\MailMotorBundle\Gateway\SubscriberGateway;
use MailMotor\Bundle\MailMotorBundle\Helper\Subscriber;

/**
 * CampaignMonitor Subscriber Gateway
 *
 * @author Jeroen Desloovere <info@jeroendesloovere.be>
 */
class CampaignMonitorSubscriberGateway implements SubscriberGateway
{
    /** @var \CS_REST_Subscribers - The external CreateSend API for CampaignMonitor */
    protected $api;

    /** @var string */
    protected $listId;

    public function __construct(string $apiKey, string $listId)
    {
        $this->listId = $listId;

        // Define API
        $this->api = new \CS_REST_Subscribers(
            $listId,
            array(
                'api_key' => $apiKey,
            )
        );
    }

    public function exists(string $email, string $listId): bool
    {
        try {
            // Will set list id when it's different then the default listId
            $this->setListId($listId);

            /** @var \CS_REST_Wrapper_Result $result A successful response will be empty */
            $result = $this->api->get($email);

            return $result->was_successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getInterests(string $listId): array
    {
        // Campaign monitor has no interests functionality
        return array();
    }

    public function hasStatus(string $email, string $listId, string $status): bool
    {
        try {
            // Will set list id when it's different then the default listId
            $this->setListId($listId);

            /** @var \CS_REST_Wrapper_Result $result A successful response will be empty */
            $result = $this->api->get($email);

            if (!property_exists($result->response, 'State')) {
                return false;
            }

            switch ($status) {
                case Subscriber::MEMBER_STATUS_UNSUBSCRIBED:
                    return (in_array($result->response->State, array('Unconfirmed', 'Unsubscribed', 'Bounced', 'Deleted')));
                    break;
                case Subscriber::MEMBER_STATUS_SUBSCRIBED:
                    return ($result->response->State === 'Active');
                    break;
                default:
                    return false;
                    break;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Subscribe
     *
     * @param string $email
     * @param string $listId
     * @param string $language
     * @param array $mergeFields
     * @param array $interests The array is like: ['9AS489SQF' => true, '4SDF8S9DF1' => false]
     * @param bool $doubleOptin Members need to validate their emailAddress before they get added to the list
     * @return boolean
     */
    public function subscribe(
        string $email,
        string $listId,
        string $language,
        array $mergeFields,
        array $interests,
        bool $doubleOptin
    ): bool {
        // Will set list id when it's different then the default listId
        $this->setListId($listId);

        // Define name
        $name = array_key_exists('FNAME', $mergeFields) ? $mergeFields['FNAME'] : '';

        // Init custom fields
        $customFields = array();

        // Loop all merge fields
        foreach ($mergeFields as $key => $value) {
            // Add custom field
            $customFields[] = [
                'Key' => $key,
                'Value' => $value,
            ];
        }

        /** @var \CS_REST_Wrapper_Result $result A successful response will be empty */
        $result = $this->api->add(array(
            'EmailAddress' => $email,
            'Name' => $name,
            'CustomFields' => $customFields,
            'Resubscribe' => true,
            'RestartSubscriptionBasedAutoResponders' => $doubleOptin,
        ));

        return $result->was_successful();
    }

    public function unsubscribe(string $email, string $listId): bool
    {
        // Will set list id when it's different then the default listId
        $this->setListId($listId);

        /** @var \CS_REST_Wrapper_Result $result A successful response will be empty */
        $result = $this->api->unsubscribe($email);

        return $result->was_successful();
    }

    private function setListId(string $listId): void
    {
        // We only set the list id, when another list id is given
        if ($listId !== $this->listId) {
            // Set list id
            $this->api->set_list_id($listId);
        }
    }
}
