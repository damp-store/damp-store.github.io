{if !isset($content_only) || !$content_only}
            </div><!-- #center_column -->
            {if isset($left_column_size) && !empty($left_column_size)}
              <div id="left_column" class="column col-xs-12 col-sm-{$left_column_size|intval}">{$HOOK_LEFT_COLUMN}</div>
            {/if}
            </div><!--.large-left-->
          </div><!--.row-->
          {if isset($right_column_size) && !empty($right_column_size)}
            <div id="right_column" class="col-xs-12 col-sm-{$right_column_size|intval} column">{$HOOK_RIGHT_COLUMN}</div>
          {/if}
          </div><!-- .row -->
        </div><!-- #columns -->
        {assign var='displayMegaHome' value={hook h='tmMegaLayoutHome'}}
          {if $displayMegaHome && $page_name == 'index'}
              {hook h='tmMegaLayoutHome'}
          {else}
              {if isset($HOOK_HOME) && $HOOK_HOME|trim}
                  <div class="clearfix">{$HOOK_HOME}</div>
              {/if}
          {/if}
      </div><!-- .columns-container -->
      {assign var='displayMegaFooter' value={hook h='tmMegaLayoutFooter'}}
        {if isset($HOOK_FOOTER) || $displayMegaFooter}
            <div class="footer-container">
                {if $displayMegaFooter}
                    <div id="footer">
                        {$displayMegaFooter}
                    </div>
                {else}
                    <div id="footer" class="container">
                        <div class="row">{$HOOK_FOOTER}</div>
                    </div>
                {/if}
            </div>
        {/if}
    </div><!-- #page -->
  {/if}

  {include file="$tpl_dir./global.tpl"}
  </body>
</html>