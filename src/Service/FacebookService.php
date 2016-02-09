<?php
namespace Wheniwork\Feedback\Service;

use RuntimeException;
use Facebook\Facebook;

class FacebookService
{
    private $fb;
    private $app_token;
    private $page_id;

    public function __construct(Facebook $fb, $page_id)
    {
        $this->fb = $fb;
        $this->page_id = $page_id;
    }

    public function authenticate($app_id, $app_secret)
    {
        $response = $this->get('/oauth/access_token', [
            'client_id' => $app_id,
            'client_secret' => $app_secret,
            'grant_type' => 'client_credentials'
        ], false);
        $this->app_token = $response['access_token'];
    }

    private function get($endpoint, $params = [], $needs_authentication = true)
    {
        $request = $endpoint;
        if (!empty($params)) {
            $request .= '?' . http_build_query($params);
        }

        if ($needs_authentication) {
            if (empty($this->app_token)) {
                throw new RuntimeException('You must authenticate before performing this request.');
            }
            $this->fb->setDefaultAccessToken($this->app_token);
        }
        $response = $this->fb->get($request);
        return $response->getDecodedBody();
    }

    private function sortByDate(&$array, $key, $ascending = true)
    {
        usort($array, function ($a, $b) use ($key, $ascending) {
            $sort = strtotime($a[$key]) - strtotime($b[$key]);
            if ($ascending) {
                return $sort;
            } else {
                return $sort * -1;
            }
        });
    }

    /**
     * Get comments that are replies to other comments.
     *
     * @param int $after_time The time after which to retrieve comments.
     */
    public function getReplyComments($after_time = 0)
    {
        $results = $this->get("/$this->page_id", [
            'fields' => 'posts{comments{comments}}'
        ]);

        $replies = [];
        foreach ($results['posts']['data'] as $post) {
            if (empty($post['comments'])) {
                continue;
            }

            foreach ($post['comments']['data'] as $comment) {
                if (empty($comment['comments'])) {
                    continue;
                }

                foreach ($comment['comments']['data'] as $reply) {
                    if (strtotime($reply['created_time']) <= $after_time) {
                        continue;
                    }

                    $replies[] = $reply;
                }
            }
        }

        $this->sortByDate($replies, 'created_time', false);

        return $replies;
    }

    /**
     * Get the parent of a given comment.
     *
     * @param int $comment_id The id of the comment whose parent should be retrieved.
     */
    public function getCommentParent($comment_id)
    {
        $results = $this->get("/$comment_id", [
            'fields' => 'parent'
        ]);

        if (empty($results['parent'])) {
            throw new RuntimeException("Comment $comment_id does not have a parent.");
        }
        return $results['parent'];
    }

    /**
     * Get the id of this service's page.
     * @return int The page id.
     */
    public function getPageId()
    {
        return $this->page_id;
    }
}
