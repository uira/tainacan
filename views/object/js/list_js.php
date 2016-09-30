<script>
    $(function () {
        var src = $('#src').val();
        var dataTable_options = {
            "language": {
                sInfo: "<?php _e('Showing from', 'tainacan'); ?>" + " _START_ " + "<?php _e('until', 'tainacan'); ?>"
                + " _END_ " + "<?php _e('until', 'tainacan'); ?>" + " _TOTAL_ " + "<?php _e('items', 'tainacan'); ?>",
                sLengthMenu: "<?php _e('Show', 'tainacan'); ?>" + " _MENU_ " + "<?php _e('items per page', 'tainacan'); ?>",
                sInfoFiltered: "(filtrados de _MAX_ eventos)",
                search: "<?php _e('Search: ', 'tainacan'); ?>",
                paginate: {
                    first: "<?php _e('First', 'tainacan'); ?>",
                    previous: "<?php _e('Previous', 'tainacan'); ?>",
                    next: "<?php _e('Next ', 'tainacan'); ?>",
                    last: "<?php _e('Last', 'tainacan'); ?>"
                }
            }
        };

        var per_page = $("#items-per-page").val();
        if( per_page && !isNaN(parseInt(per_page) ) ) {
            var items_per_page = parseInt( per_page );
        }

        $('input[name="meta_id_table"]').each(function(idx, el) {
            var valor = $(el).val();
            var nome = $("#collection_single_ordenation option[value='"+valor+"'").text();
            $('tr.dynamic-table-metas').prepend('<th>' + nome + '</th>');
        });
        var qtd_table_metas = $('input[type="hidden"][name="meta_id_table"]').length;
        if ( qtd_table_metas > 0 ) {
            var meta_table_set = true;
            if(qtd_table_metas > 7 ) {
                $("#table-view").css("display", "block");
            }
        } else {
            $('tr.dynamic-table-metas').prepend('<th>' + '<?php _e("Title", "tainacan"); ?>'  + '</th>');
            meta_table_set = false;
        }

        var total_objs = $('.object_id').length;
        $('.object_id').each(function(idx, el) {
            var c_id = $(this).val();
            var item_order = parseInt( $("#object_" + c_id).attr('data-order') );
            var actions = $("#object_" + c_id + " .item-funcs").html();

            var _table_html = "<tr>";
            if(meta_table_set) {
                var item_table_metas = $('#object_' + c_id + ' input[type="hidden"][name="item_table_meta"]');
                $( item_table_metas ).each(function(n, meta) {
                    var meta_val = $(meta).val() || "--";
                    _table_html += "<td>" + meta_val + "</td>";
                });
            } else {
                var title = $.trim($("#object_" + c_id + " .item-display-title a").text());
                var prepare_item = "<a class='tview-title' data-id='"+c_id+"' href='javascript:void(0)'>"+title+" </a>";

                _table_html += "<td>" + prepare_item + "</td>";
            }
            _table_html += "<td style='width: 10%'> <ul>" + actions + "</ul> </td> </tr>";
            $( "#table-view-elements" ).append( _table_html );

            if( items_per_page && items_per_page >= 10 ) {
                if( item_order > items_per_page) {
                    $("#object_" + c_id).hide();
                }
            }
            if( total_objs == (idx+1) ) {
                $("#table-view").DataTable(dataTable_options);
            }
        });

        $('.tview-title').on('click', function() {
            var i_id = $(this).attr("data-id");
            showSingleObject(i_id, src);
        });

        $('.pagination_items').jqPagination({
            link_string: '/?page={page_number}',
            max_page: $('#number_pages').val(),
            paged: function (page) {
                $('html,body').animate({scrollTop: 0}, 'slow');
                var current_mode = $('.selected-viewMode').attr('class').split(" ")[0];
                wpquery_page(page, current_mode);
            }
        });

        $("#items-per-page").on('change', function() {
           var limit = parseInt(this.value);
           var viewMode = $("#temp-viewMode").val();
           var container = $('.' + viewMode +'-view-container');
           $('span.per-page').text(limit);

           $(container).each(function(idx, el) {
              var item_num = parseInt( $(el).attr('data-order') );
              if( $.isNumeric( item_num ) ) {
                  if ( item_num <= limit ) {
                      $(el).show();
                  } else {
                      $(el).hide();
                  }
              }
           });
        });

        var default_viewMode = $("#default-viewMode").val();
        setCollectionViewIcon(default_viewMode);

        if (default_viewMode === "slideshow") {
            // getSlideshowTime();
            getCollectionSlideshow();
        } else if(default_viewMode === "table") {
            $("#center_pagination").hide();
        }
        $('.viewMode-control li').removeClass('selected-viewMode');
        $('.viewMode-control li.' + default_viewMode).addClass('selected-viewMode');

        function get_colorScheme() {
            var coll_id = $('#collection_id').val();
            $.ajax({
                type: "POST",
                url: src + "/controllers/collection/collection_controller.php",
                data: {operation: 'get_default_color_scheme', collection_id: coll_id}
            }).done(function (r) {
                var color_scheme = $.parseJSON(r);
                if (color_scheme) {
                    $('#accordion .title-pipe').css('border-left-color', color_scheme.secondary);
                    $('.item-funcs li a').css('color', color_scheme.primary);

                    $('.prime-color-bg').css('background', color_scheme.primary);
                    $('.prime-color').css('color', color_scheme.secondary);
                    $('.sec-color-bg').css('background', color_scheme.secondary);
                    $('.sec-color').css('color', color_scheme.secondary);
                } else {
                    $('#div_left .expand-all').css('background', '#79a6ce');
                }
            });
        }
        get_colorScheme();

        $("#container_three_columns").removeClass('white-background');
        setMenuContainerHeight();

        $(".droppableClassifications").droppable({
            hoverClass: "drophover",
            addClasses: true,
            //    tolerance: "pointer",
            over: function (event, ui) {
                //logMsg("droppable.over, %o, %o", event, ui);
            },
            drop: function (event, ui) {
                var object_id = $(this).closest('div').find('.object_id').val();
                if (object_id == null) {
                    object_id = $(this).siblings().first().attr("id")
                            .replace("add_classification_allowed_", "")
                            .replace("modal_share_network", "");
                }
                if ($('#add_classification_allowed_' + object_id).val() == '1') {
                    var source = ui.helper.data("dtSourceNode") || ui.draggable;
                    var key = source.data.key;
                    var n = key.toString().indexOf("_");
                    var value_id = '';
                    var type = ' ';
                    if (n > 0) {// se for propriedade de objeto
                        values = key.split("_");
                        if (values[1] === 'facet') {
                            showAlertGeneral('<?php _e('Atention', 'tainacan') ?>', '<?php _e('You may not classificate objects with root categories, object properties and tags', 'tainacan') ?>', 'error');
                            return;
                        }
                        else if (values[1] === 'tag') {
                            type = 'tag';
                            value_id = values[0];
                        } else {
                            type = values[1];
                            value_id = values[0];
                        }
                    } else {
                        type = 'category';
                        value_id = key.toString();
                    }

                    $.ajax({
                        type: "POST",
                        url: $('#src').val() + "/controllers/event/event_controller.php",
                        data: {
                            operation: 'add_event_classification_create',
                            socialdb_event_create_date: '<?php echo mktime(); ?>',
                            socialdb_event_user_id: $('#current_user_id').val(),
                            socialdb_event_classification_object_id: object_id,
                            socialdb_event_classification_term_id: value_id,
                            socialdb_event_classification_type: type,
                            socialdb_event_collection_id: $('#collection_id').val()}
                    }).done(function (result) {
                        elem_first = jQuery.parseJSON(result);
                        set_containers_class($('#collection_id').val());
                        show_classifications(object_id);
                        showAlertGeneral(elem_first.title, elem_first.msg, elem_first.type);

                    });
                } else {
                    showAlertGeneral('<?php _e('Attention', 'tainacan') ?>', '<?php _e('Action not allowed by admin!', 'tainacan') ?>', 'info');
                }
            },
            activate: function (event, ui) {
                $(this).css('border', '3px dashed black');
                // $(this).addClass("ui-state-highlight").find("p").hover();
                //$(".cat").removeClass("categorias");
                //$(".row cat").show();
            },
            deactivate: function (event, ui) {
                $(this).css('border-style', 'none');
                // $(this).addClass("ui-state-highlight").find("p").hover();
                //  $(".categorias").hide();
                //  $(".categorias").hover();
            }
        });

        $('a.move_trash').on('click', function() {
            var bulk_type = $('input.bulk_action').val();
            if( bulk_type === 'select_all' ) {
                var collect_id = $("#collection_id").val();
                clean_collection( '<?php _e("Clean Collection", "tainacan") ?>', '<?php _e("Are you sure to remove all items", "tainacan") ?>', collect_id );
            } else if(bulk_type === "select_some") {
                var selected_total = $('.selected-item').length;
                var bulkds = [];
                $('.selected-item').each(function(idx, el) {
                    var item_id = $(el).parent().attr("id").replace("object_", "");
                    bulkds.push(item_id);
                });

                if( selected_total > 0 ) {
                    var collection_id = $('#collection_id').val();
                    var main_title = '<?php _e("Attention","tainacan"); ?>';
                    var desc = '<?php _e("Send ", "tainacan"); ?>' + selected_total + '<?php _e(" items to trash?", "tainacan"); ?>';
                    move_items_to_trash( main_title, desc, bulkds, collection_id);
                } else {
                    showAlertGeneral('<?php _e('Attention', 'tainacan') ?>', '<?php _e("You did not select any items to delete!", "tainacan") ?>', 'info');
                }
            }
        });
        
        $('a.move_edition').on('click', function() {
            var edit_data = [];
            show_modal_main();
            
            $('.list-mode-set').hide();
            $('.selected-item').each(function(idx, el) {
                var item_id = $(el).parent().attr("id").replace("object_", "");
                var item_title = $("#object_" + item_id + " h4.item-display-title").text().trim();
                var item_desc = $("#object_" + item_id + " .item-description").text().trim();
                edit_data.push( { id: item_id, title: item_title, desc: item_desc } );
            });

            $.ajax({
                type: "POST",
                url: $('#src').val() + "/controllers/object/object_controller.php",
                data: { operation: 'edit_multiple_items', items_data: edit_data }
            }).done(function(html_res){
                hide_modal_main();
                $("#main_part").html(html_res);
            });
        });

        $('.selectable-items').on('click', '.selectors a', function(ev) {
            var select = $(this).attr("class").split(" ")[0];
            $('input.bulk_action').val( select );
            var its_highlighted = $( $(this).siblings()[0]).hasClass('highlight');
            var action =  $('input.bulk_action').val();

            $(this).toggleClass('highlight');

            if ( its_highlighted && "select_all" === action ) {
            }
            $( $(this).siblings()[0]).removeClass('highlight');
            $('.selectable-actions').fadeIn();
        });

        $('.item-colecao').click(function() {
            if( $(this).hasClass('selecting-item') ) {
                $(this).toggleClass('selected-item');
            }
        });

    });

    function set_toastr_class() {
        return { positionClass: 'toast-bottom-right', preventDuplicates: true };
    }

     function select_some() {
         if( ! $('.item-colecao').hasClass('selecting-item') ) {
             toastr.info('<?php _e('Select items below to edit or exclude!', 'tainacan') ?>', '', set_toastr_class());
         }

         $('.object_id').each(function(idx, el) {
            var item = $("#object_" + $(el).val() );
            $(item).find('.item-colecao').addClass('selecting-item');
         });
    }

    function select_all() {
        $(".item-colecao").removeClass('selected-item');
        toastr.info('<?php _e('All items have been selected!', 'tainacan') ?>', '', set_toastr_class());
        $('.object_id').each(function(idx, el) {
            var item = $("#object_" + $(el).val() );
            $(item).find(".item-colecao").toggleClass('selected-item');
        });
    }

    function show_info(id) {
        check_privacity_info(id);
        list_ranking(id);
        list_files(id);
        list_properties(id);
        list_properties_edit_remove(id);
        $("#more_info_show_" + id).toggle();
        $("#less_info_show_" + id).toggle();
        $("#all_info_" + id).toggle('slow');
    }

    function showPopover(id) {
        // pop up #example1, #example2, #example3 with same content
        $('#popover_network' + id).popover({
            html: true,
            content: function () {
                return $('#popover_content_wrapper' + id).html();
            }
        });
    }

    function showModalShareNetwork(id) {
        var $_modal_id = $('.in').attr('id');
        if ($_modal_id == 'collection-slideShow') {
            $('#modal_share_network' + id).addClass('slideshow-mode');
            $('.in').modal('hide');
        }
        $('#modal_share_network' + id).modal('show');
        init_autocomplete('#collections_object_share' + id);
    }

    $('.modal-share-network').on('hidden.bs.modal', function () {
        if ($(this).hasClass('slideshow-mode')) {
            $("#collection-slideShow").modal('show');
        }
    });

    function send_share_item(id) {
        if ($('#email_object_share' + id).val().trim() !== '' || $('#collections_object_share' + id).val().trim() !== '') {
            show_modal_main();
            $.ajax({
                type: "POST",
                url: $('#src').val() + "/controllers/user/user_controller.php",
                data: {
                    collection_id: $('#collection_id').val(),
                    operation: 'share_item_email_or_collection',
                    object_id: id,
                    email: $('#email_object_share' + id).val(),
                    new_collection: $('#collections_object_share' + id + '_id').val()}
            }).done(function (result) {
                hide_modal_main();
                elem_first = jQuery.parseJSON(result);
                showAlertGeneral(elem_first.title, elem_first.msg, elem_first.type);
                if (elem_first.type && elem_first.type === 'success') {
                    window.location = $('#collections_object_share' + id + '_url').val();
                }
            });
        } else {
            showAlertGeneral('<?php _e('Atention', 'tainacan') ?>', '<?php _e('You need to fill the email or choose the collection', 'tainacan') ?>', 'error');
        }
    }

//BEGIN: funcao para mostrar os arquivos
    function list_files(id) {
        $.ajax({
            type: "POST",
            url: $('#src').val() + "/controllers/object/object_controller.php",
            data: {collection_id: $('#collection_id').val(), operation: 'show_files', object_id: id}
        }).done(function (result) {
            $('#list_files_' + id).html(result);
            $('.dropdown-toggle').dropdown();
            $('.nav-tabs').tab();
        });
    }

    function list_ranking(id) {
        $.ajax({
            type: "POST",
            url: $('#src').val() + "/controllers/ranking/ranking_controller.php",
            data: {collection_id: $('#collection_id').val(), operation: 'list_ranking_object', object_id: id}
        }).done(function (result) {
            $('#list_ranking_' + id).html(result);
            $('.dropdown-toggle').dropdown();
            $('.nav-tabs').tab();
        });
    }

//BEGIN:as proximas funcoes sao para mostrar os eventos
// list_properties(id): funcao que mostra a primiera listagem de propriedades
    function list_properties(id) {
        $.ajax({
            type: "POST",
            url: $('#src').val() + "/controllers/object/object_controller.php",
            data: {collection_id: $('#collection_id').val(), operation: 'list_properties', object_id: id}
        }).done(function (result) {
            $('#list_all_properties_' + id).html(result);
            $('.dropdown-toggle').dropdown();
            $('.nav-tabs').tab();
        });
    }
// mostra a listagem apos clique no botao para edicao e exclusao
    function list_properties_edit_remove(id) {
        $.ajax({
            type: "POST",
            url: $('#src').val() + "/controllers/object/object_controller.php",
            data: {collection_id: $('#collection_id').val(), operation: 'list_properties_edit_remove', object_id: id}
        }).done(function (result) {
            $('#list_properties_edit_remove').html(result);
            $('.dropdown-toggle').dropdown();
            $('.nav-tabs').tab();
        });
    }
// mostra o formulario para criacao de propriedade de dados
    function show_form_data_property(object_id) {
        $.ajax({
            type: "POST",
            url: $('#src').val() + "/controllers/object/object_controller.php",
            data: {collection_id: $('#collection_id').val(), operation: 'show_form_data_property', object_id: object_id}
        }).done(function (result) {
            $('#data_property_form_' + object_id).html(result);
            $('#list_all_properties_' + object_id).hide();
            $('#object_property_form_' + object_id).hide();
            $('#edit_data_property_form_' + object_id).hide();
            $('#edit_object_property_form_' + object_id).hide();
            $('#data_property_form_' + object_id).show();
            $('.dropdown-toggle').dropdown();
            $('.nav-tabs').tab();
        });
    }
// mostra o formulario para criacao de propriedade de objeto
    function show_form_object_property(object_id) {
        $.ajax({
            type: "POST",
            url: $('#src').val() + "/controllers/object/object_controller.php",
            data: {collection_id: $('#collection_id').val(), operation: 'show_form_object_property', object_id: object_id}
        }).done(function (result) {
            $('#object_property_form_' + object_id).html(result);
            $('#list_all_properties_' + object_id).hide();
            $('#data_property_form_' + object_id).hide();
            $('#edit_data_property_form_' + object_id).hide();
            $('#edit_object_property_form_' + object_id).hide();
            $('#object_property_form_' + object_id).show();
            $('.dropdown-toggle').dropdown();
            $('.nav-tabs').tab();
        });
    }
// funcao acionando no bolta voltar que mostra a listagem principal
    function back_button(object_id) {
        $('#data_property_form_' + object_id).hide();
        $('#object_property_form_' + object_id).hide();
        $('#edit_data_property_form_' + object_id).hide();
        $('#edit_object_property_form_' + object_id).hide();
        $('#list_all_properties_' + object_id).show();
    }
// END:fim das funcoes que mostram as propriedades
//funcao que mostra as classificacoes apos clique no botao show_classification
    function show_classifications(object_id) {
        var close_box = "<a href='javascript:void(0)' class='close-metadata-box' onclick='toggle_item_box_elements(" + object_id + ")'>" +
                "<span class='glyphicon glyphicon-remove-circle'></span></a>";

        $.ajax({
            type: "POST",
            url: $('#src').val() + "/controllers/object/object_controller.php",
            data: {collection_id: $('#collection_id').val(), operation: 'show_classifications', object_id: object_id}
        }).done(function (result) {
            toggle_item_box_elements(object_id);
            $('#classifications_' + object_id).html(close_box + result).fadeIn();
            $('#show_classificiations_' + object_id).fadeOut();
            $('.dropdown-toggle').dropdown();
            $('.nav-tabs').tab();
        });
    }

    function toggle_item_box_elements(object_id) {
        var elements = ['.item-display-title', '.item-description', '.item-author', '.item-creation'];
        $.each(elements, function (index, element) {
            $('#classifications_' + object_id).parent('.item-meta').find(element).toggle();
        });

        $('#classifications_' + object_id + ' .close-metadata-box').toggle();
        $('#classifications_' + object_id).toggleClass('shown-classifications').toggle();
        $('#show_classificiations_' + object_id).fadeIn();
    }

//mostrar modal de denuncia
    function show_report_abuse(object_id) {
        $('#modal_delete_object' + object_id).modal('show');
    }
//mostrar modal de duplicacao
    function show_duplicate_item(object_id) {
        $('#modal_duplicate_object' + object_id).modal('show');
        init_autocomplete('#other_collections' + object_id);
    }

    function showOtherCollectionField(object_id) {
        $('#other_collections' + object_id).show();
        $('#version_motive' + object_id).hide();
    }
    function showVersionMotiveField(object_id) {
        $('#other_collections' + object_id).hide();
        $('#version_motive' + object_id).show();
    }
    function hideAllFieldsDuplicate(object_id) {
        $('#other_collections' + object_id).hide();
        $('#version_motive' + object_id).hide();
    }

    function send_duplicate_item(object_id) {
        //console.log($('input[name=duplicate_item]:checked', '#formDuplicateItem'+object_id).val());
        if ($('input[name=duplicate_item]:checked', '#formDuplicateItem' + object_id).val() == 'this_collection') {
            //Duplicate in this collection
            $('#modalImportMain').modal('show');//mostro o modal de carregamento
            $.ajax({
                type: "POST",
                url: $('#src').val() + "/controllers/object/object_controller.php",
                data: {collection_id: $('#collection_id').val(),
                    operation: 'duplicate_item_same_collection',
                    object_id: object_id
                }
            }).done(function (result) {
                /*$('#modalImportMain').modal('hide');//escondo o modal de carregamento
                 $('#modal_duplicate_object' + object_id).modal('hide');
                 $("#container_socialdb").hide('slow');
                 $("#form").hide().html(result).show('slow');
                 $('#create_button').hide();
                 $('.dropdown-toggle').dropdown();
                 $('.nav-tabs').tab();*/
                $('#modalImportMain').modal('hide');
                $('#modal_duplicate_object' + object_id).modal('hide');
                //hide_modal_main();
                $("#form").html('');
                $('#main_part').hide();
                $('#display_view_main_page').hide();
                $('#loader_collections').hide();
                $('#configuration').html(result).show();
                $('.dropdown-toggle').dropdown();
                $('.nav-tabs').tab();
            });
        } else if ($('input[name=duplicate_item]:checked', '#formDuplicateItem' + object_id).val() == 'other_collection') {
            //Duplicate in other collections
            $('#modalImportMain').modal('show');//mostro o modal de carregamento
            $.ajax({
                type: "POST",
                url: $('#src').val() + "/controllers/object/object_controller.php",
                data: {collection_id: $('#collection_id').val(),
                    new_collection_id: $('#other_collections' + object_id + '_id').val(),
                    new_collection_url: $('#other_collections' + object_id + '_url').val(),
                    operation: 'duplicate_item_other_collection',
                    object_id: object_id
                }
            }).done(function (result) {
                //$('#modalImportMain').modal('hide');//escondo o modal de carregamento
                //$('#modal_duplicate_object' + object_id).modal('hide');
                json = jQuery.parseJSON(result);
                window.location.replace(json.new_collection_url);
                /*$("#container_socialdb").hide('slow');
                 $("#form").hide().html(result).show('slow');
                 $('#create_button').hide();
                 $('.dropdown-toggle').dropdown();
                 $('.nav-tabs').tab();*/
            });
        } else if ($('input[name=duplicate_item]:checked', '#formDuplicateItem' + object_id).val() == 'versioning') {
            //Versioning
            $('#modalImportMain').modal('show');//mostro o modal de carregamento
            $.ajax({
                type: "POST",
                url: $('#src').val() + "/controllers/object/object_controller.php",
                data: {collection_id: $('#collection_id').val(),
                    operation: 'versioning',
                    motive: $('#version_motive' + object_id).val(),
                    object_id: object_id
                }
            }).done(function (result) {
                $('#modalImportMain').modal('hide');
                $('#modal_duplicate_object' + object_id).modal('hide');
                if (result) {
                    wpquery_clean();
                    showAlertGeneral('<?php _e('Success', 'tainacan') ?>', '<?php _e('Successfully created version.', 'tainacan') ?>', 'success');
                } else {
                    showAlertGeneral('<?php _e('Error', 'tainacan') ?>', '<?php _e('Please try again.', 'tainacan') ?>', 'error');
                }
            });
        }
    }

// editando objeto
    function edit_object(object_id) {
        $('#modalImportMain').modal('show');//mostro o modal de carregamento
        $.ajax({
            type: "POST",
            url: $('#src').val() + "/controllers/object/object_controller.php",
            data: {collection_id: $('#collection_id').val(), operation: 'edit_default', object_id: object_id}
        }).done(function (result) {
            $('#modalImportMain').modal('hide');//escondo o modal de carregamento
            $("#container_socialdb").hide('slow');
            $("#form").hide().html(result).show('slow');
            $('#create_button').hide();
            $('.dropdown-toggle').dropdown();
            $('.nav-tabs').tab();
        });
    }

    function edit_object_item(object_id) {
        $('#modalImportMain').modal('show');//mostro o modal de carregamento
        $.ajax({
            type: "POST",
            url: $('#src').val() + "/controllers/object/object_controller.php",
            data: {collection_id: $('#collection_id').val(), operation: 'edit', object_id: object_id}
        }).done(function (result) {
            hide_modal_main();
            $("#form").html('');
            $('#main_part').hide();
            $('#display_view_main_page').hide();
            $('#loader_collections').hide();
            $('#configuration').html(result).show();
            $('.dropdown-toggle').dropdown();
            $('.nav-tabs').tab();
        });
    }

    function redirect_facebook(object_id) {
        $.ajax({
            type: "POST",
            url: $('#src').val() + "/controllers/ranking/ranking_controller.php",
            data: {fb_id: $('#socialdb_fb_api_id').val(), collection_id: $('#collection_id').val(), operation: 'redirect_facebook', object_id: object_id}
        }).done(function (result) {
            json = jQuery.parseJSON(result);
            window.open(json.redirect, '_blank');
            // window.location = json.redirect;
        });
    }

    $('button.cards-ranking').on('click', function () {
        var object_id = $(this).attr("id").replace("show_rankings_", "");
        var order_id = $('#collection_single_ordenation').val();
        var col_id = $('#collection_id').val();

        $.ajax({
            type: "POST", url: $('#src').val() + "/controllers/ranking/ranking_controller.php",
            data: {collection_id: col_id, ordenation_id: order_id, operation: 'list_value_ordenation', object_id: object_id}
        }).done(function (result) {
            $(this).hide();
            $("#rankings_" + object_id).html(result).show();
            var $_cards_ranking = $("#rankings_" + object_id).html();
            var $_other_rankings = [$("#r_list_" + object_id), $("#r_gallery_" + object_id), $("#r_slideshow_" + object_id)];

            $($_other_rankings).each(function (idx, el) {
                $($_cards_ranking).appendTo(el);
            });
        });
    });
    $('button.cards-ranking').each(function (idx, el) {
        $(this).hide().click();
    });


    function check_privacity_info(id) {
        $.ajax({
            url: $('#src').val() + '/controllers/collection/collection_controller.php',
            type: 'POST',
            data: {operation: 'check_privacity', collection_id: id}
        }).done(function (result) {
            elem = jQuery.parseJSON(result);
            if (elem.privacity == false)
            {
                redirect_privacity(elem.title, elem.msg, elem.url);
            }
        });
    }

    function showModalCreateCollection() {
        $('#myModal').modal('show');
    }

    var col_title = $('.titulo-colecao h3.title').text();
    $("#collection-slideShow .sS-collection-name").text(col_title);

    var default_slideshow_time;
    if ($("#slideshow-time").val() !== "") {
        default_slideshow_time = $("#slideshow-time").val().replace('st-', '').replace('-secs', '');
        default_slideshow_time *= 1000;
    } else {
        default_slideshow_time = 4000;
    }

    var main_slick_settings = {
        slidesToShow: 1,
        slidesToScroll: 1,
        arrows: true,
        fade: true,
        asNavFor: '.collection-slides',
        adaptiveHeight: true
    };
    var collection_slick_settings = {
        slidesToShow: 5,
        slidesToScroll: 1,
        asNavFor: '.main-slide',
        variableWidth: true,
        dots: true,
        centerMode: true,
        arrows: false,
        adaptiveHeight: true,
        autoplay: true,
        autoplaySpeed: default_slideshow_time,
        focusOnSelect: true
    };

    $('.main-slide').slick(main_slick_settings);
    $('.collection-slides').slick(collection_slick_settings);

</script>
