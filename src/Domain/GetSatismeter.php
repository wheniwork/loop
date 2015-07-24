<?php
namespace Wheniwork\Feedback\Domain;

use Wheniwork\Feedback\Service\SatismeterService;

class GetSatismeter extends FeedbackDomain
{
    const SATISMETER_REDIS_KEY = "satismeter_last_time";

    public function __invoke(array $input)
    {
        $payload = $this->getPayload();

        try {
            // Initialize Redis key if necessary
            if (empty($this->getLastResponseTime())) {
                $startOfDay = strtotime("midnight");
                $this->saveLastResponseTime($startOfDay);
            }

            // Get new responses since we last checked
            $responses = SatismeterService::getResponses($this->getLastResponseTime());

            // Set the time of the latest response in Redis
            if (count($responses) > 0) {
                $this->saveLastResponseTime(strtotime(reset($responses)->created));
            }

            // Process new responses
            $output = ['new_responses' => []];
            foreach ($responses as $response) {
                if (!empty($response->feedback)) {
                    $this->createFeedback($response->feedback, "Satismeter");
                    array_push($output['new_responses'], $response);
                }
            }

            $payload->setStatus($payload::SUCCESS);
            $payload->setOutput($output);
        } catch (Exception $e) {
            $payload->setStatus($payload::ERROR);
            $payload->setOutput($e);
        }

        return $payload;
    }

    private function getLastResponseTime() {
        return $this->redis->get(self::SATISMETER_REDIS_KEY);
    }

    private function saveLastResponseTime($time) {
        $this->redis->set(self::SATISMETER_REDIS_KEY, $time);
    }
}
