<?php
namespace Wheniwork\Feedback\Service;

use Facebook\Facebook;

class FacebookService
{
    private static function getFacebook()
    {
        $fb = new Facebook([
            'app_id' => $_ENV['FB_APP_ID'],
            'app_secret' => $_ENV['FB_APP_SECRET'],
            'default_graph_version' => 'v2.4',
        ]);
        $fb->setDefaultAccessToken($_ENV['FB_TOKEN']);
        return $fb;
    }

    private static function get($request) {
        $fb = self::getFacebook();
        $response = $fb->get($request);
        return $response->getDecodedBody();
    }

    private static function sortByDate(&$array, $key, $ascending = true) {
        usort($array, function($a, $b) use ($key, $ascending) {
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
    public static function getReplyComments($after_time = 0)
    {
        $results = self::get('/' . $_ENV['FB_PAGE_ID'] . '?fields=posts{comments{comments}}');

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

        self::sortByDate($replies, 'created_time');

        return $replies;
    }

    /**
     * Get the parent of a given comment.
     *
     * @param int $comment_id The id of the comment whose parent should be retrieved.
     */
    public static function getCommentParent($comment_id) {
        $results = self::get('/' . $comment_id . '?fields=parent');

        if (empty($results['parent'])) {
            throw new RuntimeException("Comment $comment_id does not have a parent.");
        }
        return $results['parent'];
    }
    
}
