/**
 * Editorial Calendar JavaScript
 *
 * @package NewsPlugin
 */

(function($) {
    'use strict';

    /**
     * Editorial Calendar Manager
     */
    const EditorialCalendar = {
        
        /**
         * Initialize the calendar
         */
        init: function() {
            this.bindEvents();
            this.loadCalendar();
        },
        
        /**
         * Bind event handlers
         */
        bindEvents: function() {
            $(document).on('click', '.calendar-item', this.handleItemClick);
            $(document).on('change', '.workflow-status', this.handleStatusChange);
            $(document).on('change', '.workflow-priority', this.handlePriorityChange);
            $(document).on('change', '.workflow-assignee', this.handleAssigneeChange);
            $(document).on('click', '.save-workflow', this.saveWorkflow);
        },
        
        /**
         * Load calendar data
         */
        loadCalendar: function() {
            const startDate = $('#calendar-start').val() || moment().startOf('month').format('YYYY-MM-DD');
            const endDate = $('#calendar-end').val() || moment().endOf('month').format('YYYY-MM-DD');
            
            $.ajax({
                url: newsEditorial.apiUrl + 'calendar',
                method: 'GET',
                data: {
                    start: startDate,
                    end: endDate
                },
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', newsEditorial.nonce);
                },
                success: function(data) {
                    EditorialCalendar.renderCalendar(data);
                },
                error: function(xhr, status, error) {
                    console.error('Failed to load calendar:', error);
                }
            });
        },
        
        /**
         * Render calendar data
         */
        renderCalendar: function(data) {
            const $calendar = $('#editorial-calendar');
            $calendar.empty();
            
            if (data.length === 0) {
                $calendar.html('<p>No editorial items found for this period.</p>');
                return;
            }
            
            // Group by date
            const groupedData = this.groupByDate(data);
            
            // Render calendar
            Object.keys(groupedData).forEach(date => {
                const $dateGroup = $('<div class="calendar-date-group"></div>');
                $dateGroup.append(`<h3 class="calendar-date">${moment(date).format('MMMM D, YYYY')}</h3>`);
                
                const $items = $('<div class="calendar-items"></div>');
                groupedData[date].forEach(item => {
                    $items.append(this.renderCalendarItem(item));
                });
                
                $dateGroup.append($items);
                $calendar.append($dateGroup);
            });
        },
        
        /**
         * Group data by date
         */
        groupByDate: function(data) {
            const grouped = {};
            
            data.forEach(item => {
                const date = moment(item.deadline).format('YYYY-MM-DD');
                if (!grouped[date]) {
                    grouped[date] = [];
                }
                grouped[date].push(item);
            });
            
            return grouped;
        },
        
        /**
         * Render calendar item
         */
        renderCalendarItem: function(item) {
            const priorityClass = `priority-${item.priority}`;
            const statusClass = `status-${item.status}`;
            
            return $(`
                <div class="calendar-item ${priorityClass} ${statusClass}" data-post-id="${item.id}">
                    <div class="item-header">
                        <h4 class="item-title">${item.title}</h4>
                        <span class="item-status">${this.getStatusLabel(item.status)}</span>
                    </div>
                    <div class="item-meta">
                        <span class="item-priority priority-${item.priority}">${this.getPriorityLabel(item.priority)}</span>
                        ${item.assignee ? `<span class="item-assignee">${item.assignee.name}</span>` : ''}
                    </div>
                    <div class="item-actions">
                        <a href="${item.url}" class="button button-small">Edit</a>
                    </div>
                </div>
            `);
        },
        
        /**
         * Handle item click
         */
        handleItemClick: function(e) {
            e.preventDefault();
            const $item = $(this);
            const postId = $item.data('post-id');
            
            EditorialCalendar.showItemDetails(postId);
        },
        
        /**
         * Show item details
         */
        showItemDetails: function(postId) {
            // Load item details via AJAX
            $.ajax({
                url: newsEditorial.apiUrl + 'calendar',
                method: 'GET',
                data: { post_id: postId },
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', newsEditorial.nonce);
                },
                success: function(data) {
                    EditorialCalendar.renderItemModal(data);
                },
                error: function(xhr, status, error) {
                    console.error('Failed to load item details:', error);
                }
            });
        },
        
        /**
         * Render item modal
         */
        renderItemModal: function(item) {
            const $modal = $(`
                <div class="editorial-modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2>${item.title}</h2>
                            <button class="modal-close">&times;</button>
                        </div>
                        <div class="modal-body">
                            <div class="workflow-controls">
                                <div class="control-group">
                                    <label>Status:</label>
                                    <select class="workflow-status" data-post-id="${item.id}">
                                        <option value="draft" ${item.status === 'draft' ? 'selected' : ''}>Draft</option>
                                        <option value="assigned" ${item.status === 'assigned' ? 'selected' : ''}>Assigned</option>
                                        <option value="in_progress" ${item.status === 'in_progress' ? 'selected' : ''}>In Progress</option>
                                        <option value="review" ${item.status === 'review' ? 'selected' : ''}>Review</option>
                                        <option value="approved" ${item.status === 'approved' ? 'selected' : ''}>Approved</option>
                                        <option value="published" ${item.status === 'published' ? 'selected' : ''}>Published</option>
                                    </select>
                                </div>
                                <div class="control-group">
                                    <label>Priority:</label>
                                    <select class="workflow-priority" data-post-id="${item.id}">
                                        <option value="low" ${item.priority === 'low' ? 'selected' : ''}>Low</option>
                                        <option value="normal" ${item.priority === 'normal' ? 'selected' : ''}>Normal</option>
                                        <option value="high" ${item.priority === 'high' ? 'selected' : ''}>High</option>
                                        <option value="urgent" ${item.priority === 'urgent' ? 'selected' : ''}>Urgent</option>
                                    </select>
                                </div>
                                <div class="control-group">
                                    <label>Assignee:</label>
                                    <select class="workflow-assignee" data-post-id="${item.id}">
                                        <option value="">Select Assignee</option>
                                        <!-- Options will be populated via AJAX -->
                                    </select>
                                </div>
                                <div class="control-group">
                                    <label>Notes:</label>
                                    <textarea class="workflow-notes" data-post-id="${item.id}" rows="3">${item.notes || ''}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="button button-primary save-workflow" data-post-id="${item.id}">Save Changes</button>
                            <button class="button modal-close">Cancel</button>
                        </div>
                    </div>
                </div>
            `);
            
            $('body').append($modal);
            this.loadAssignees();
        },
        
        /**
         * Load assignees
         */
        loadAssignees: function() {
            $.ajax({
                url: newsEditorial.apiUrl + 'authors',
                method: 'GET',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', newsEditorial.nonce);
                },
                success: function(data) {
                    const $select = $('.workflow-assignee');
                    data.authors.forEach(author => {
                        $select.append(`<option value="${author.id}">${author.name}</option>`);
                    });
                }
            });
        },
        
        /**
         * Handle status change
         */
        handleStatusChange: function(e) {
            const $select = $(this);
            const postId = $select.data('post-id');
            const status = $select.val();
            
            EditorialCalendar.updateWorkflow(postId, { status: status });
        },
        
        /**
         * Handle priority change
         */
        handlePriorityChange: function(e) {
            const $select = $(this);
            const postId = $select.data('post-id');
            const priority = $select.val();
            
            EditorialCalendar.updateWorkflow(postId, { priority: priority });
        },
        
        /**
         * Handle assignee change
         */
        handleAssigneeChange: function(e) {
            const $select = $(this);
            const postId = $select.data('post-id');
            const assignee = $select.val();
            
            EditorialCalendar.updateWorkflow(postId, { assignee: assignee });
        },
        
        /**
         * Save workflow
         */
        saveWorkflow: function(e) {
            e.preventDefault();
            const $button = $(this);
            const postId = $button.data('post-id');
            
            const data = {
                post_id: postId,
                status: $(`.workflow-status[data-post-id="${postId}"]`).val(),
                priority: $(`.workflow-priority[data-post-id="${postId}"]`).val(),
                assignee: $(`.workflow-assignee[data-post-id="${postId}"]`).val(),
                notes: $(`.workflow-notes[data-post-id="${postId}"]`).val()
            };
            
            EditorialCalendar.updateWorkflow(postId, data);
        },
        
        /**
         * Update workflow
         */
        updateWorkflow: function(postId, data) {
            $.ajax({
                url: newsEditorial.apiUrl + 'calendar',
                method: 'POST',
                data: data,
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', newsEditorial.nonce);
                },
                success: function(response) {
                    if (response.success) {
                        EditorialCalendar.showNotice('Workflow updated successfully', 'success');
                        EditorialCalendar.loadCalendar();
                    } else {
                        EditorialCalendar.showNotice('Failed to update workflow', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    EditorialCalendar.showNotice('Failed to update workflow: ' + error, 'error');
                }
            });
        },
        
        /**
         * Show notice
         */
        showNotice: function(message, type) {
            const $notice = $(`<div class="notice notice-${type} is-dismissible"><p>${message}</p></div>`);
            $('.editorial-calendar').prepend($notice);
            
            setTimeout(() => {
                $notice.fadeOut();
            }, 3000);
        },
        
        /**
         * Get status label
         */
        getStatusLabel: function(status) {
            const labels = {
                'draft': 'Draft',
                'assigned': 'Assigned',
                'in_progress': 'In Progress',
                'review': 'Review',
                'approved': 'Approved',
                'published': 'Published'
            };
            return labels[status] || status;
        },
        
        /**
         * Get priority label
         */
        getPriorityLabel: function(priority) {
            const labels = {
                'low': 'Low',
                'normal': 'Normal',
                'high': 'High',
                'urgent': 'Urgent'
            };
            return labels[priority] || priority;
        }
    };
    
    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        if ($('#editorial-calendar').length) {
            EditorialCalendar.init();
        }
    });
    
    /**
     * Handle modal close
     */
    $(document).on('click', '.modal-close', function() {
        $('.editorial-modal').remove();
    });
    
    /**
     * Handle modal backdrop click
     */
    $(document).on('click', '.editorial-modal', function(e) {
        if (e.target === this) {
            $('.editorial-modal').remove();
        }
    });

})(jQuery);
