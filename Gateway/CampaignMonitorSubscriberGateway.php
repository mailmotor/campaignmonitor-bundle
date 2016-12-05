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
    /**
     * The external CreateSend API for CampaignMonitor
     *
     * @var \CS_REST_Subscribers
     */
    protected $api;

    /**
     * @var string
     */
    protected $listId;

    /**
     * Construct
     *
     * @param string $apiKey
     * @param string $listId
     */
    public function __construct(
        $apiKey,
        $listId
    ) {
        $this->listId = $listId;

        // Define API
        $this->api = new \CS_REST_Subscribers(
            $listId,
            array(
                'api_key' => $apiKey,
            )
        );
    }

    /**
     * Exists
     *
     * @param string $email
     * @param string $listId
     * @return boolean
     */
    public function exists(
        $email,
        $listId
    ) {
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

    /**
     * Get interests
     *
     * @param string $listId
     * @return array
     */
    public function getInterests(
        $listId
    ) {
        // Campaign monitor has no interests functionality
        return array();
    }

    /**
     * Has status
     *
     * @param string $email
     * @param string $listId
     * @param string $status
     * @return boolean
     */
    public function hasStatus(
        $email,
        $listId,
        $status
    ) {
        try {
            // Will set list id when it's different then the default listId
            $this->setListId($listId);

            /** @var \CS_REST_Wrapper_Result $result A successful response will be empty */
            $result = $this->api->get($email);

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
     * @param boolean $doubleOptin Members need to validate their emailAddress before they get added to the list
     * @return boolean
     */
    public function subscribe(
        $email,
        $listId,
        $language,
        $mergeFields,
        $interests,
        $doubleOptin
    ) {
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

    /**
     * Unsubscribe
     *
     * @param string $email
     * @param string $listId
     * @return boolean
     */
    public function unsubscribe(
        $email,
        $listId
    ) {
        // Will set list id when it's different then the default listId
        $this->setListId($listId);

        /** @var \CS_REST_Wrapper_Result $result A successful response will be empty */
        $result = $this->api->unsubscribe($email);

        return $result->was_successful();
    }

    /**
     * Set list id
     *
     * @var string $listId
     */
    private function setListId($listId)
    {
        // We only set the list id, when another list id is given
        if ($listId !== $this->listId) {
            // Set list id
            $this->api->set_list_id($listId);
        }
    }
}
