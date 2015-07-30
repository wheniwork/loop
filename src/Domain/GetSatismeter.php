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
                $this->saveLastResponseTime(strtotime(reset($responses)->created) + 1);
            }

            // Process new responses
            $output = ['new_responses' => []];
            foreach ($responses as $response) {
                if (!empty($response->feedback)) {
                    $score = $response->rating;
                    $body = $response->feedback;
                    $email = $response->user->email;
                    $tone = self::NEUTRAL;
                    if ($response->category == "promoter") {
                        $tone = self::POSITIVE;
                    } else if ($response->category == "passive") {
                        $tone = self::PASSIVE;
                    } else if ($response->category == "detractor") {
                        $tone = self::NEGATIVE;
                    }

                    $this->createFeedback("<strong>$score/10.</strong> $body <i>(From $email)</i>", "Satismeter", $tone);
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
