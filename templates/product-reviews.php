<?php

/**
* TrustFeed Reviews and Customer Feedback for WooCommerce
*
* @version 1.0 
*/



global $product;

if( ! empty( $review ) ) {	

	$trustfeed_dev_credits = $review['dev_credits'];		 

	$review_count = $review['review_count'];

	$average = $review['average'];

	$rating = $review['rating'];

	$ratingstars = $review['ratingstars'];

	$ratingstarwidth = $review['ratingstarwidth'];

	$starfive = $review['starfive'];

	$starfour = $review['starfour'];

	$starthree = $review['starthree'];

	$startwo = $review['startwo'];

	$starone = $review['starone'];

	$tagQuestions_array  = $review['tagQuestions_array'];

	$reviewer_array  = $review['reviewer_array'];

?>		

	<script type="text/javascript">	

	jQuery(document).ready(function($) {		

		var tabdiv = $(".woocommerce-Tabs-panel--reviews").width();		

		if( tabdiv < 640 ){			

			$('.tf-left').removeClass('col-lg-4');			

			$('.tf-left').addClass('col-lg-12');			

			$('.tf-right').removeClass('col-lg-8');			

			$('.tf-right').addClass('col-lg-12');

			$('.reviewscount').removeClass('col-lg-12 col-md-4 col-sm-4 col-xs-12');			

			$('.reviewscount').addClass('col-lg-12 col-md-12 col-sm-12 col-xs-12');		

			$('.tf-progress-starbar').removeClass('col-lg-12 col-md-8 col-sm-8 col-xs-12');			

			$('.tf-progress-starbar').addClass('col-lg-12 col-md-12 col-sm-12 col-xs-12');	

			$('.tf-progress').removeClass('col-lg-12 col-md-8 col-sm-8 col-xs-12');			

			$('.tf-progress').addClass('col-lg-12 col-md-12 col-sm-12 col-xs-12');		

		}	

	})	

	</script>	

	<div id="trustfeed-reviews" class="pradeep tf-reviews">		

		<div class="tf-row">			

			<div class="tf-left col-lg-4 col-md-12 col-sm-12 col-xs-12">				

				<div class="reviewscount col-lg-12 col-md-4 col-sm-4 col-xs-12">					

					<h1><?php echo $rating; ?></h1>					

					<div class="tf-out5">out of 5</div>					

					<div class="tf-star">						

						<div class="tf-star-rating">							

							<div class="star-width" style="width: <?php  echo $ratingstarwidth; ?>%"></div>						

						</div> 					

					</div>						

					<div class="tf-review-count"><?php echo $review_count; ?> Reviews </div>				

				</div>					

				<div class="tf-progress-starbar col-lg-12 col-md-8 col-sm-8 col-xs-12">					

					<div class="tf-progress-left">						

						<div class="tf-progress-star">							

							<div class="progress-star-line"><i class="fa fa-star" aria-hidden="true"></i></div>						

						</div>						

						<div class="tf-progress-star">							

							<div class="progress-star-line"><i class="fa fa-star" aria-hidden="true"></i></div>							

							<div class="progress-star-line"><i class="fa fa-star" aria-hidden="true"></i></div>						

						</div>						

						<div class="tf-progress-star">							

							<div class="progress-star-line"><i class="fa fa-star" aria-hidden="true"></i></div>							

							<div class="progress-star-line"><i class="fa fa-star" aria-hidden="true"></i></div>							

							<div class="progress-star-line"><i class="fa fa-star" aria-hidden="true"></i></div>						

						</div>						

						<div class="tf-progress-star">							

							<div class="progress-star-line"><i class="fa fa-star" aria-hidden="true"></i></div>							

							<div class="progress-star-line"><i class="fa fa-star" aria-hidden="true"></i></div>							

							<div class="progress-star-line"><i class="fa fa-star" aria-hidden="true"></i></div>							

							<div class="progress-star-line"><i class="fa fa-star" aria-hidden="true"></i></div>						

						</div>						

						<div class="tf-progress-star">							

							<div class="progress-star-line"><i class="fa fa-star" aria-hidden="true"></i></div>							

							<div class="progress-star-line"><i class="fa fa-star" aria-hidden="true"></i></div>							

							<div class="progress-star-line"><i class="fa fa-star" aria-hidden="true"></i></div>							

							<div class="progress-star-line"><i class="fa fa-star" aria-hidden="true"></i></div>							

							<div class="progress-star-line"><i class="fa fa-star" aria-hidden="true"></i></div>						

						</div>					

					</div>					

					<div class="tf-progress-right"> 						

						<div class="tf-progress-bar">								

							<div class="progress-bar-line">								

								<div class="tf-progress-text"><?php echo $starfive; ?>%</div> 								

								<div class="tf-progress-width" role="progressbar" aria-valuenow="<?php echo $starfive; ?>" aria-valuemin="0" aria-valuemax="100" style="width:<?php echo $starfive; ?>%"></div>							

							</div>						

						</div>						

						<div class="tf-progress-bar">								

							<div class="progress-bar-line">								

								<div class="tf-progress-text"><?php echo $starfour; ?>%</div> 								

								<div class="tf-progress-width" role="progressbar" aria-valuenow="<?php echo $starfour; ?>" aria-valuemin="0" aria-valuemax="100" style="width:<?php echo $starfour; ?>%"></div>							

							</div>						

						</div>						

						<div class="tf-progress-bar">								

							<div class="progress-bar-line">

								<div class="tf-progress-text"><?php echo $starthree; ?>%</div> 								

								<div class="tf-progress-width" role="progressbar" aria-valuenow="<?php echo $starthree; ?>" aria-valuemin="0" aria-valuemax="100" style="width:<?php echo $starthree; ?>%"></div>							

							</div>						

						</div>						

						<div class="tf-progress-bar">								

							<div class="progress-bar-line">								

								<div class="tf-progress-text"><?php echo $startwo; ?>%</div> 								

								<div class="tf-progress-width" role="progressbar" aria-valuenow="<?php echo $startwo; ?>" aria-valuemin="0" aria-valuemax="100" style="width:<?php echo $startwo; ?>%"></div>							

							</div>						

						</div>						

						<div class="tf-progress-bar">								

							<div class="progress-bar-line">								

								<div class="tf-progress-text"><?php echo $starone; ?>%</div> 								

								<div class="tf-progress-width" role="progressbar" aria-valuenow="<?php echo $starone; ?>" aria-valuemin="0" aria-valuemax="100" style="width:<?php echo $starone; ?>%"></div>							

							</div>						

						</div>					

					</div>				

				</div>				

				<div class="tf-question col-lg-12 col-md-12 col-sm-12 col-xs-12">

					<?php

					$positiveRatedTags_array = array();

					$negativeRatedTags_array = array();

					foreach ($tagQuestions_array as $key => $value) {

						$positiveRatedTags_array[]= $value[0]->positiveRatedTags;

						$negativeRatedTags_array[]= $value[0]->negativeRatedTags;

					} 

					foreach ($positiveRatedTags_array as $arrayName => $arrayData){

						$result_tags_array[$arrayName] = array();

						foreach ($arrayData as $value){

							if (empty($result_tags_array[$arrayName][$value->text])){

								$result_tags_array[$arrayName][$value->text] = 1;

							}else{

								$result_tags_array[$arrayName][$value->text]++;

							}

						}

						arsort($result_tags_array[$arrayName]);

					}

					$final_tags_array = array();

					foreach ($result_tags_array as $k=>$tagsvalue) {

						foreach ($tagsvalue as $id=>$value) {

							$final_tags_array[$id]+=$value;

						}

					}

					arsort($final_tags_array);

					//$final_tags = array_slice($final_tags_array, 0, 3);

					foreach ($final_tags_array as $key => $value) {

						echo '<a href="javascript:void(0)">'.$key.' ('. $value .')</a>';

					} 

				?>

				</div>

				<?php if($trustfeed_dev_credits){ ?>															

					<p class="tf-powerlogo">Powered by<img src="<?php echo plugin_dir_url( dirname( __FILE__ ) ) . 'assets/images/logo_power.png'; ?>"></p>								

				<?php } ?>

			</div>

			<div class="tf-right col-lg-8 col-md-12 col-sm-12 col-xs-12">

				<div class="tf-user-bg">

					<div class="tf-bg-order-left"></div>

					<div class="tf-bg-order-right"></div>

				</div>

				<div class="tf-user-middle">

					<?php 

					foreach( $reviewer_array as $reviews ){ ?> 

						<div class="tf-chat-user" id="li-comment-<?php echo $reviews['id']; ?>">

							<div class="tf-chat-user-img">

							<?php 

							$comment_date = $reviews['date'];

							$reviewer	= $reviews['reviewer'];																

							$firstname = $reviews['reviewer']->firstname; 								

							$lastname = $reviews['reviewer']->lastname;								

							if($firstname){	?>									

								<div class="darkCircle"><span class="full-name"><?php echo substr($firstname, 0, 1); ?>&nbsp;<?php echo substr($lastname, 0, 1); ?></span></div>

							<?php }else{ ?>									

								<div class="darkCircle"><span class="full-name"><?php echo substr($reviewer, 0, 1); ?></span></div>								

							<?php } ?>

							</div>

							<div class="tf-chat-user-content">

								<div class="tf-content-inner">

									<h2><?php if($firstname){ echo $firstname; }else{ echo $reviewer; } ?></h2>

									<div class="tf-star">						

									<?php 

									$totalstarreview = count($reviews['starRating']);								

									$totalstarRating = array();

									foreach( $reviews['starRating'] as $starRating ){									

										$totalstarRating[] = $starRating->ratingStars;								

									}								

									$totalratingStars = array_sum($totalstarRating);								

									$ratingStars = $totalratingStars / $totalstarreview;																		 

									$rating = $ratingStars / 5;								

									$commentaverage = $rating*100;	

										echo '<div class="tf-star-rating"><div class="star-width" style="width: '.$commentaverage.'%"></div></div>';

									?>

									</div> 					

								</div>

							</div>

							<div style="padding:0;" class="tf-review-content  col-lg-8 col-md-12 col-sm-12 col-xs-12">

								<div class="tf-comment-intro"><?php echo date('F j, Y, g:i a', strtotime($comment_date)); ?></div>

								<div class="tf-review-text"><?php echo $reviews['textQuestions'][0]->reviewText; ?></div>

							</div>

						</div>

					<?php 

					}

					?>

				</div>	

			</div>

		</div>

	</div>

<?php 

}else{

	echo  "No Review Found";

}	

?>