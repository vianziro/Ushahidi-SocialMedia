<?php 
/**
 * Twitter view page.
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team <team@ushahidi.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @module     API Controller
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */
?>
				<h2>
					<?php admin::messages_subtabs("socialmedia"); ?>
				</h2>


<!-- tabs -->
				<div class="tabs">
					<!-- tabset -->
					<ul class="tabset">
							<li><a href="?tab=<?php echo SocialMedia_Message_Model::STATUS_TOREVIEW; ?>" <?php if ($tab == SocialMedia_Message_Model::STATUS_TOREVIEW) echo "class=\"active2\""; ?>><?php echo Kohana::lang('socialmedia.messages.to_review'); ?> (<?php echo $count_to_review; ?>)</a></li>
							<li><a href="?tab=<?php echo SocialMedia_Message_Model::STATUS_POTENTIAL; ?>" <?php if ($tab == SocialMedia_Message_Model::STATUS_POTENTIAL) echo "class=\"active2\""; ?>><?php echo Kohana::lang('socialmedia.messages.potential_reports'); ?> (<?php echo $count_potential; ?>)</a></li>
							<li><a href="?tab=<?php echo SocialMedia_Message_Model::STATUS_REPORTED; ?>" <?php if ($tab == SocialMedia_Message_Model::STATUS_REPORTED) echo "class=\"active2\""; ?>><?php echo Kohana::lang('socialmedia.messages.reported'); ?> (<?php echo $count_reported; ?>)</a></li>
							<li><a href="?tab=<?php echo SocialMedia_Message_Model::STATUS_SPAM; ?>" <?php if ($tab == SocialMedia_Message_Model::STATUS_SPAM) echo "class=\"active2\""; ?>><?php echo Kohana::lang('ui_main.spam');?> (<?php echo $count_spam; ?>)</a></li>
							<li><a href="?tab=<?php echo SocialMedia_Message_Model::STATUS_DISCARDED; ?>" <?php if ($tab == SocialMedia_Message_Model::STATUS_DISCARDED) echo "class=\"active2\""; ?>><?php echo Kohana::lang('socialmedia.messages.discarded');?> (<?php echo $count_discarded; ?>)</a></li>
					</ul>
					<!-- tab -->
					<div class="tab">
						<ul>
							<li><a href="#" onClick="socialMediaAction('d', '<?php echo utf8::strtoupper(Kohana::lang('socialmedia.messages.discarded'));?>', '')"><?php echo utf8::strtoupper(Kohana::lang('socialmedia.messages.discard'));?></a></li>
							<li><a href="#" onClick="socialMediaAction('p', '<?php echo utf8::strtoupper(Kohana::lang('socialmedia.messages.potential_report'));?>', '')"><?php echo utf8::strtoupper(Kohana::lang('socialmedia.messages.potential_report'));?></a></li>

						<?php if ($tab != SocialMedia_Message_Model::STATUS_SPAM) : ?>
							<li><a href="#" onClick="socialMediaAction('s', '<?php echo utf8::strtoupper(Kohana::lang('ui_main.spam'));?>', '')"><?php echo utf8::strtoupper(Kohana::lang('ui_main.spam'));?></a></li>
						<?php else: ?>
							<li><a href="#" onClick="socialMediaAction('n', '<?php echo utf8::strtoupper(Kohana::lang('ui_main.not_spam'));?>', '')"><?php echo utf8::strtoupper(Kohana::lang('ui_main.not_spam'));?></a></li>
						<?php endif; ?>

						</ul>

						<div class="sort_by" style="background-color: #9b0000; padding: 10px 10px;"><a style="color:#fff" href="/admin/messages/socialmedia/tool"><?php echo utf8::strtoupper(Kohana::lang('socialmedia.messages.tool.launch'));?></a></div>
					</div>
				</div>
				<?php 
				if ($form_error) {
				?>
					<!-- red-box -->
					<div class="red-box">
						<h3><?php echo Kohana::lang('ui_main.error');?></h3>
						<ul><?php echo Kohana::lang('ui_main.select_one');?></ul>
					</div>
				<?php
				}

				if ($form_saved) {
				?>
					<!-- green-box -->
					<div class="green-box" id="submitStatus">
						<h3><?php echo Kohana::lang('ui_main.messages');?> <?php echo $form_action; ?> <a href="#" id="hideMessage" class="hide"><?php echo Kohana::lang('ui_main.hide_this_message');?></a></h3>
					</div>
				<?php
				}
				?>
				<!-- report-table -->
				<?php  
					print form::open(NULL, array('id' => 'socialMediaMain', 'name' => 'socialMediaMain')); ?>
					<input type="hidden" name="action" id="action" value="">
					<input type="hidden" name="message_single" id="message_single" value="">

					<div class="table-holder">
						<table class="table">
							<thead>
								<tr>
									<th class="col-1"><input id="checkallmessage" type="checkbox" class="check-box" onclick="CheckAll( this.id, 'message_id[]' )" /></th>
									<th class="col-2"><?php echo Kohana::lang('ui_main.message_details');?></th>
									<th class="col-3"><?php echo Kohana::lang('ui_main.date');?></th>
									<th class="col-4"><?php echo Kohana::lang('ui_main.actions');?></th>
								</tr>
							</thead>
							<tfoot>
								<tr class="foot">
									<td colspan="4">
										<?php echo $pagination; ?>
									</td>
								</tr>
							</tfoot>
							<tbody>
								<?php
								if ($total_items == 0)
								{
								?>
									<tr>
										<td colspan="4" class="col">
											<h3><?php echo Kohana::lang('ui_main.no_results');?></h3>
										</td>
									</tr>
								<?php
								}

								foreach ($entries as $entry)
								{
									$entry_id = $entry->id;
									$entry_link = $entry->getData("url");
									$service = str_replace("SocialMedia ", "", $entry->reporter->service->service_name);
									$entry_description = $entry->message_detail;
									$entry_from = trim($entry->reporter->reporter_first . " " . $entry->reporter->reporter_last);
									$entry_from .= empty($entry->reporter->reporter_email) ? null : " (" . $entry->reporter->reporter_email . ")";
									$entry_date = date('Y-m-d g:ia', strtotime($entry->message_date));
									$incident_id = $entry->incident_id;
									?>
									<tr>
										<td class="col-1"><input name="message_id[]" id="message_id" value="<?php echo $entry_id; ?>" type="checkbox" class="check-box"/></td>
										<td class="col-2">
											<div class="post"><?php echo $entry_description; ?></div>

											<ul class="info">
												<li class="none-separator"><?php echo Kohana::lang('ui_main.from');?>: <?php echo $service; ?> - <strong><a href="<?php echo $entry_link; ?>" target="_blank"><?php echo $entry_from; ?></a></strong></li>
											<?php if (! empty($entry->longitude) && !empty($entry->latitude)): ?>
												<li><strong><?php echo Kohana::lang('socialmedia.messages.geotagged');?></strong></li>
											<?php endif; ?>
											<?php if ($entry->reporter->level_id == SocialMedia_Message_Model::STATUS_TRUSTED): ?>
												<li><?php echo Kohana::lang('socialmedia.messages.trusted_reporter');?></li>
											<?php endif; ?>
											</ul>

											<?php if ($entry->message_level == SocialMedia_Message_Model::STATUS_INREVIEW): ?>
											<ul class="info">
												<li class="none-separator"><?php echo Kohana::lang('socialmedia.messages.message_in_review');?></li>
											</ul>
											<?php endif; ?>

											<?php if ($entry->Socialmedia_Asset->count() > 0): ?>
												<ul class="links">
													<?php foreach ($entry->Socialmedia_Asset as $media): ?>
														<li style="float:none;">
														<?php if (empty($show_images) || $media->type != "photo"): ?>
															<?php echo Kohana::lang('socialmedia.messages.assets.' . $media->type); ?>: 
															<a target="_blank" href="<?php echo $media->url; ?>"><?php echo $media->url; ?></a>
														<?php else: ?>
															<a target="_blank" href="<?php echo $media->url; ?>"><img src="<?php echo $media->url; ?>" height="100"/></a>															
														<?php endif; ?>													
														</li>
													<?php endforeach; ?>
												</ul>
											<?php endif; ?>
										</td>
										<td class="col-3"><?php echo $entry_date; ?></td>
										<td class="col-4">
											<ul>
												<?php
												if ($incident_id != 0) {
													echo "<li class=\"none-separator\"><a href=\"". url::site() . 'admin/reports/edit/' . $incident_id . "\" class=\"status_yes\"><strong>".Kohana::lang('ui_main.view_report')."</strong></a></li>";
												}
												else
												{
													echo "<li class=\"none-separator\"><a href=\"". url::site() . 'admin/messages/socialmedia/report/' . $entry_id ."\">".Kohana::lang('ui_main.create_report')."?</a></li>";

													if ($entry->message_level == SocialMedia_Message_Model::STATUS_SPAM) 
													{
														echo "<li>" . Kohana::lang('ui_main.spam') . "</li>";
													} 
													else 
													{
														echo "<li><a class='del' href=\"#\" onclick=\"socialMediaAction('s', 'SPAM', " . $entry->id . ")\">" . Kohana::lang('ui_main.spam') . "?</a></li>";
													} 

													if ($entry->message_level == SocialMedia_Message_Model::STATUS_POTENTIAL) 
													{
														echo "<li class=\"none-separator\">" . Kohana::lang('socialmedia.messages.potential_report') . "</li>";
													} 
													else 
													{
														echo "<li class=\"none-separator\"><a href=\"#\" onclick=\"socialMediaAction('p', 'POTENTIAL', " . $entry->id . ")\">" . Kohana::lang('socialmedia.messages.potential_report') . "?</a></li>";
													}

													if ($entry->message_level == SocialMedia_Message_Model::STATUS_DISCARDED) 
													{
														echo "<li>" . Kohana::lang('socialmedia.messages.discard') . "</li>";
													} 
													else
													{
														echo "<li><a href=\"#\" onclick=\"socialMediaAction('d', 'DISCARDED', " . $entry->id . ")\">".Kohana::lang('socialmedia.messages.discard')."?</a></li>";
													}

												}
												?>
												<li>
                                                <!-- <a href="<?php echo url::site().'admin/messages/delete/'.$entry_id ?>" onclick="return confirm("<?php echo Kohana::lang('ui_main.action_confirm');?>")" class="del"><?php echo Kohana::lang('ui_main.delete');?></a> --></li>
											</ul>
										</td>
									</tr>
									<?php
								}
								?>
							</tbody>
						</table>
					</div>

					<div class="tabs">
					<div class="tab">
						<ul>
							<li><a href="#" onClick="socialMediaAction('d', '<?php echo utf8::strtoupper(Kohana::lang('socialmedia.messages.discarded'));?>', '')"><?php echo utf8::strtoupper(Kohana::lang('socialmedia.messages.discard'));?></a></li>
							<li><a href="#" onClick="socialMediaAction('p', '<?php echo utf8::strtoupper(Kohana::lang('socialmedia.messages.potential_report'));?>', '')"><?php echo utf8::strtoupper(Kohana::lang('socialmedia.messages.potential_report'));?></a></li>

						<?php if ($tab != SocialMedia_Message_Model::STATUS_SPAM) : ?>
							<li><a href="#" onClick="socialMediaAction('s', '<?php echo utf8::strtoupper(Kohana::lang('ui_main.spam'));?>', '')"><?php echo utf8::strtoupper(Kohana::lang('ui_main.spam'));?></a></li>
						<?php else: ?>
							<li><a href="#" onClick="socialMediaAction('n', '<?php echo utf8::strtoupper(Kohana::lang('ui_main.not_spam'));?>', '')"><?php echo utf8::strtoupper(Kohana::lang('ui_main.not_spam'));?></a></li>
						<?php endif; ?>

						</ul>
					</div>
					</div>
				</div>
				<?php print form::close(); ?>