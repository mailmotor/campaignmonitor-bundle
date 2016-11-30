<?php

namespace MailMotor\Bundle\CampaignMonitorBundle\Gateway;

use MailMotor\Bundle\MailMotorBundle\Gateway\SubscriberGateway;

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
     * @var \CS_REST_General
     */
    protected $api;

    /**
     * Construct
     *
     * @param string $apiKey
     */
    public function __construct(
        $apiKey
    ) {
        // We require the CreateSend API
        require_once(__DIR__ . '/../../../campaignmonitor/createsend-php/CS_REST_General.php');

        // Define API
        $this->api = new \CS_REST_General(array(
            'api_key' => $apiKey,
        ));
    }

    /**
     * Get a subscriber
     *
     * @param string $email
     * @param string $listId
     * @return array
     */
    public function get(
        $email,
        $listId
    ) {
        try {
            // todo
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
        try {
           // todo
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get interest category id
     *
     * @param string $interestCategoryId
     * @param string $listId
     * @return array
     */
    protected function getInterestsForCategoryId(
        $interestCategoryId,
        $listId
    ) {
        try {
            // todo
        } catch (\Exception $e) {
            return false;
        }
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
        // todo
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

        // Set list id
        $this->api->set_list_id($listId);

        /** @var CS_REST_Wrapper_Result $result A successful response will be empty */
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
        // todo
    }
}
