<?php get_header(); ?>

<?php PC_Hooks::pc_after_get_header(); /* Framework hook wrapper */ ?>

	<div id="container">

		<?php PC_Hooks::pc_after_container(); /* Framework hook wrapper */ ?>

		<div id="contentwrap" <?php echo PC_Utility::contentwrap_layout_classes(); ?>>

			<?php PC_Hooks::pc_before_content(); /* Framework hook wrapper */ ?>

			<div class="<?php echo PC_Utility::content_layout_classes_primary(); ?>">

				<?php PC_Hooks::pc_after_content_open(); /* Framework hook wrapper */ ?>

				<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

					<div id="post-<?php the_ID(); ?>" <?php post_class( 'post' ); ?>>

						<div class="post-aside">
							<p class="post-date"><?php the_time( 'M' ); ?><br /><?php the_time( 'j' ); ?></p>

							<p class="author">By <?php the_author(); ?></p>

							<?php PC_Hooks::pc_pre_post_meta(); /* Framework hook wrapper */ ?>
						</div>

						<div class="post-content">

							<h1 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>

							<div class="post-meta">
								<?php PC_Hooks::pc_post_meta(); /* Framework hook wrapper */ ?>
								<p>
									<span class="categories"><?php echo get_the_term_list( get_the_ID(), 'portfolio_group', '', ', ', '' ); ?></span>
									<?php global $post; ?>
									<?php if ( 'open' == $post->comment_status ) : ?>
										<span class="comments">| <?php comments_popup_link( __( 'Leave a Comment', 'presscoders' ), __( '1 Comment', 'presscoders' ), __( '% Comments', 'presscoders' ), '', '' ); ?></span>
									<?php endif; ?>
								</p>
								<?php PC_Hooks::pc_after_post_meta(); /* Framework hook wrapper */ ?>
							</div>
							<!-- .post-meta -->

							<?php if ( has_post_thumbnail() ) : ?>
								<div class="post-thumb">
									<?php
									$post_id = get_the_ID();
									echo PC_Utility::get_responsive_slider_image( $post_id, 'post-thumbnail' ); /* Show post thumbnail image, if one exists. */
									?>
								</div> <!-- .post-thumb -->
							<?php endif; ?>

							<?php
							the_content( '' );
							wp_link_pages( array( 'before' => '<div class="page-link">', 'after' => '</div>' ) );
							?>
						</div>
						<!-- post-content -->
					</div> <!-- post-item -->

					<?php comments_template( '', true ); ?>

				<?php endwhile; // end of the loop. ?>

					<?php wp_reset_postdata(); ?>

					<div class="navigation">
						<div class="alignleft"><?php next_posts_link() ?></div>
						<div class="alignright"><?php previous_posts_link() ?></div>
					</div>

				<?php endif; ?>

			</div>
			<!-- .content -->

			<?php PC_Hooks::pc_after_content(); /* Framework hook wrapper */ ?>

		</div>
		<!-- #contentwrap -->

	</div><!-- #container -->

<?php get_footer(); ?>