<?php
/*
        Article Replication to NIFTY Cloud mobile backend  v0.0.1
*/
/*
        Copyright 2015 nd.yuya

        Licensed under the Apache License, Version 2.0 (the "License");
        you may not use this file except in compliance with the License.
        You may obtain a copy of the License at

                http://www.apache.org/licenses/LICENSE-2.0

        Unless required by applicable law or agreed to in writing, software
        distributed under the License is distributed on an "AS IS" BASIS,
        WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
        See the License for the specific language governing permissions and
        limitations under the License.
*/

require_once(NCMBArticleReplication_PLUGIN_DIR . 'ncmb-client.php');

class NCMBArticleReplicationImplements {

	public function __construct() {
		add_action('transition_post_status', array($this, 'on_transition_post_status'), 10, 3);
	}

	public function on_transition_post_status($new_status, $old_status, $post) {
		if ($new_status != 'inherit') {
			$replicate_data = array(
				'article_id' => $post->ID,
				'title' => $post->post_title,
				'content' => $post->post_content,
				'url' => $post->guid,
				'status' => $post->post_status,
			);

			$this->replicate_article($post->ID, $replicate_data);
		}
	}

	public function replicate_article($article_id, $data) {
		$options = get_option('ncmb_article_replication_option');
		$ncmb_client = new NCMBClient($options['application_key'], $options['client_key']);

		$query_string = http_build_query(
			array('where' => json_encode(array(
				'article_id' => $article_id,
			)))
		);
		$search_results_string = $ncmb_client->get('/classes/WPArticle?' . $query_string);
		$search_results = json_decode($search_results_string, true);

		if (count($search_results['results']) == 0) {
			$ncmb_client->post('/classes/WPArticle', json_encode($data));
		} else {
			$ncmb_client->put('/classes/WPArticle/' . $search_results['results'][0]['objectId'], json_encode($data));
		}
	}

	public function check_existence($article_id) {
		$options = get_option('ncmb_article_replication_option');
		$ncmb_client = new NCMBClient($options['application_key'], $options['client_key']);
		$search_results_string = $ncmb_client->get('/classes/WPArticle?' . $query_string);
		$search_results = json_decode($search_results_string, true);
	}
}
?>
