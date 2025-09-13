// Handle navigation between sections
document.addEventListener('DOMContentLoaded', function() {
  // Get all navigation links
  const navLinks = document.querySelectorAll('.sidebar-menu a');
  
  // Add click event listeners
  navLinks.forEach(link => {
    link.addEventListener('click', function(e) {
      const href = this.getAttribute('href');
      if (href.startsWith('#')) {
        e.preventDefault();
        
        // Remove active class from all links
        navLinks.forEach(navLink => {
          navLink.parentElement.classList.remove('active');
        });
        
        // Add active class to clicked link
        this.parentElement.classList.add('active');
        
        // Hide all content sections
        document.querySelectorAll('.content-section').forEach(section => {
          section.classList.remove('active');
        });
        
        // Show the selected section
        const targetId = href.substring(1);
        document.getElementById(targetId).classList.add('active');
      }
      // else allow normal navigation for external links
    });
  });

  // Initialize dashboard chart
  const initDashboardChart = () => {
    const ctx = document.getElementById('appointmentsChart').getContext('2d');
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
          label: 'Appointments',
          data: [12, 19, 15, 22, 18, 25],
          backgroundColor: 'rgba(244, 194, 13, 0.2)',
          borderColor: 'rgba(244, 194, 13, 1)',
          borderWidth: 2,
          tension: 0.4
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'top',
          }
        },
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });
  };

  // Appointments functionality
  const setupAppointments = () => {
    const statusFilter = document.getElementById('status-filter');
    const dateFilter = document.getElementById('date-filter');
    const resetFilters = document.getElementById('reset-filters');
    const addAppointment = document.getElementById('add-appointment');
    const appointmentRows = document.querySelectorAll('.appointments-table tbody tr');

    // Filter appointments
    const filterAppointments = () => {
      const statusValue = statusFilter.value;
      const dateValue = dateFilter.value;
      
      appointmentRows.forEach(row => {
        const rowStatus = row.querySelector('.status-badge').textContent.toLowerCase();
        const rowDate = row.cells[4].textContent;
        
        const statusMatch = statusValue === 'all' || rowStatus === statusValue;
        const dateMatch = !dateValue || rowDate === dateValue;
        
        row.style.display = statusMatch && dateMatch ? '' : 'none';
      });
    };

    // Reset filters
    resetFilters.addEventListener('click', () => {
      statusFilter.value = 'all';
      dateFilter.value = '';
      filterAppointments();
    });

    // Add event listeners
    statusFilter.addEventListener('change', filterAppointments);
    dateFilter.addEventListener('change', filterAppointments);

    // Add appointment button
    addAppointment.addEventListener('click', () => {
      // In a real app, this would open a modal/form
      alert('Add new appointment functionality would go here');
    });

    // Edit/delete buttons
    document.querySelectorAll('.btn-icon.edit').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.stopPropagation();
        const row = e.target.closest('tr');
        alert(`Would edit appointment ${row.cells[0].textContent}`);
      });
    });

    document.querySelectorAll('.btn-icon.delete').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.stopPropagation();
        const row = e.target.closest('tr');
        if (confirm(`Delete appointment ${row.cells[0].textContent}?`)) {
          row.remove();
        }
      });
    });
  };

  // Inventory functionality
  const setupInventory = () => {
    const categoryFilter = document.getElementById('category-filter');
    const stockFilter = document.getElementById('stock-filter');
    const resetFilters = document.getElementById('reset-inventory-filters');
    const addItem = document.getElementById('add-item');
    const inventoryRows = document.querySelectorAll('.inventory-table tbody tr');

    // Filter inventory
    const filterInventory = () => {
      const categoryValue = categoryFilter.value;
      const stockValue = stockFilter.value;
      
      inventoryRows.forEach(row => {
        const rowCategory = row.cells[2].textContent.toLowerCase();
        const rowStock = parseInt(row.cells[3].textContent);
        
        const categoryMatch = categoryValue === 'all' || rowCategory === categoryValue;
        let stockMatch = true;
        
        if (stockValue === 'low') {
          stockMatch = rowStock <= 5;
        } else if (stockValue === 'medium') {
          stockMatch = rowStock > 5 && rowStock <= 20;
        } else if (stockValue === 'high') {
          stockMatch = rowStock > 20;
        }
        
        row.style.display = categoryMatch && stockMatch ? '' : 'none';
      });
    };

    // Reset filters
    resetFilters.addEventListener('click', () => {
      categoryFilter.value = 'all';
      stockFilter.value = 'all';
      filterInventory();
    });

    // Add event listeners
    categoryFilter.addEventListener('change', filterInventory);
    stockFilter.addEventListener('change', filterInventory);

    // Add item button
    addItem.addEventListener('click', () => {
      alert('Add new inventory item functionality would go here');
    });

    // Edit/delete buttons
    document.querySelectorAll('.inventory-table .btn-icon.edit').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.stopPropagation();
        const row = e.target.closest('tr');
        alert(`Would edit inventory item ${row.cells[0].textContent}`);
      });
    });

    document.querySelectorAll('.inventory-table .btn-icon.delete').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.stopPropagation();
        const row = e.target.closest('tr');
        if (confirm(`Delete inventory item ${row.cells[0].textContent}?`)) {
          row.remove();
        }
      });
    });
  };

  // Reports functionality
  const setupReports = () => {
    // Detailed Report Data (mock - would come from API in real app)
    const reportData = {
      appointments: {
        today: { 
          count: 5, 
          change: '+2',
          details: [
            { time: '09:00', customer: 'Juan Dela Cruz', service: 'Oil Change' },
            { time: '10:30', customer: 'Maria Santos', service: 'Brake Inspection' },
            { time: '13:15', customer: 'Robert Lim', service: 'Tire Rotation' }
          ]
        },
        week: { 
          count: 24, 
          change: '+5',
          breakdown: {
            completed: 18,
            cancelled: 2,
            rescheduled: 4
          }
        },
        month: { 
          count: 98, 
          change: '+12',
          services: {
            maintenance: 45,
            repairs: 38,
            inspections: 15
          }
        }
      },
      revenue: {
        today: { 
          amount: 12500, 
          change: '+8%',
          transactions: [
            { id: 1001, amount: 3500, service: 'Major Service' },
            { id: 1002, amount: 2200, service: 'Brake Pads' },
            { id: 1003, amount: 6800, service: 'Transmission Repair' }
          ]
        },
        week: { 
          amount: 58420, 
          change: '+18%',
          categories: {
            services: 32400,
            parts: 19800,
            diagnostics: 6220
          }
        },
        month: { 
          amount: 215000, 
          change: '+22%',
          comparison: {
            last_month: 176000,
            last_year: 185000
          }
        }
      },
      inventory: {
        all: { 
          count: 156, 
          change: '12 low stock',
          categories: {
            fluids: 28,
            filters: 42,
            parts: 86
          }
        },
        low: { 
          count: 12, 
          change: 'Need restock',
          items: [
            'Engine Oil 5W-30',
            'Air Filters',
            'Brake Fluid'
          ]
        },
        out: { 
          count: 3, 
          change: 'Urgent',
          items: [
            'Wiper Blades (Large)',
            'Spark Plugs NGK',
            'Cabin Filter'
          ]
        }
      },
      customers: {
        new: {
          count: 8,
          change: '+3',
          sources: {
            referrals: 5,
            walkins: 2,
            online: 1
          }
        },
        returning: {
          count: 32,
          change: '+7',
          services: {
            maintenance: 25,
            repairs: 7
          }
        }
      }
    };

    // Initialize report chart
    const initReportChart = () => {
      const ctx = document.getElementById('reportChart').getContext('2d');
      return new Chart(ctx, {
        type: 'bar',
        data: {
          labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
          datasets: [{
            label: 'Appointments',
            data: [12, 19, 15, 22, 18, 25],
            backgroundColor: 'rgba(54, 162, 235, 0.5)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              position: 'top',
            }
          },
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      });
    };

    let reportChart = initReportChart();

    // Update widget data
    const updateWidget = (widgetType, period) => {
      const widget = document.getElementById(`${widgetType}-widget`);
      const data = reportData[widgetType][period];
      
      if (widgetType === 'revenue') {
        widget.querySelector('.stat-value').textContent = `₱${data.amount.toLocaleString()}`;
      } else {
        widget.querySelector('.stat-value').textContent = data.count;
      }
      widget.querySelector('.stat-change').textContent = data.change;
    };

    // Update chart visualization type
    const updateChartType = (type) => {
      reportChart.config.type = type;
      reportChart.update();
    };

    // Export report data
    const exportReport = () => {
      alert('Report exported successfully!');
    };

    // Initialize widgets with data
    updateWidget('appointments', 'week');
    updateWidget('revenue', 'week');
    updateWidget('inventory', 'all');

    // Add event listeners for widget filters
    document.querySelectorAll('.widget-filter').forEach(filter => {
      filter.addEventListener('change', function() {
        const widgetType = this.dataset.widget;
        const filterValue = this.value;
        updateWidget(widgetType, filterValue);
      });
    });

    // Add chart type toggle functionality
    document.querySelectorAll('.chart-type').forEach(button => {
      button.addEventListener('click', function() {
        const chartType = this.dataset.type;
        updateChartType(chartType);
      });
    });

    // Export report button
    document.getElementById('export-report').addEventListener('click', exportReport);

    // Keep existing report generation functionality
    const generateReportBtn = document.getElementById('generate-report');
    const reportTypeSelect = document.getElementById('report-type');
    const dateRangeSelect = document.getElementById('date-range');

    generateReportBtn.addEventListener('click', () => {
      const reportType = reportTypeSelect.value;
      const dateRange = dateRangeSelect.value;
      alert(`Generating ${reportType} report for ${dateRange}`);
    });

    reportTypeSelect.addEventListener('change', (e) => {
      // Update chart based on report type if needed
    });
  };

  // Initialize the dashboard
  initDashboardChart();
  setupAppointments();
  setupInventory();
  setupReports();
  
  // Show dashboard by default
  document.querySelector('.sidebar-menu li:first-child a').click();

  // Admin Logout functionality
document.getElementById('logout-btn').addEventListener('click', function (e) {
  e.preventDefault();

  // Show loading overlay
  const loadingOverlay = document.createElement('div');
  loadingOverlay.id = 'logoutOverlay';
  loadingOverlay.style.position = 'fixed';
  loadingOverlay.style.top = '0';
  loadingOverlay.style.left = '0';
  loadingOverlay.style.width = '100%';
  loadingOverlay.style.height = '100%';
  loadingOverlay.style.backgroundColor = 'rgba(0,0,0,0.6)';
  loadingOverlay.style.display = 'flex';
  loadingOverlay.style.justifyContent = 'center';
  loadingOverlay.style.alignItems = 'center';
  loadingOverlay.style.flexDirection = 'column';
  loadingOverlay.style.color = 'white';
  loadingOverlay.style.fontSize = '24px';
  loadingOverlay.style.zIndex = '2000';

  loadingOverlay.innerHTML = `
      <i class="fas fa-spinner fa-spin" style="font-size:50px; margin-bottom:20px;"></i>
      <p>Logging out...</p>
  `;

  document.body.appendChild(loadingOverlay);

  // After 2 seconds, redirect to logout.php (which destroys session and redirects to home.php)
  setTimeout(() => {
      window.location.href = 'logout.php';
  }, 3000);
});

  // Settings tab functionality
  document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      // Remove active class from all buttons and content
      document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
      document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
      
      // Add active class to clicked button
      this.classList.add('active');
      
      // Show corresponding content
      const tabId = this.getAttribute('data-tab');
      document.getElementById(`${tabId}-tab`).classList.add('active');
    });
  });
});
