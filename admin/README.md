# GoJourney Admin Panel

The GoJourney Admin Panel is a comprehensive backend management system for the GoJourney travel platform. It provides administrators with tools to manage hotels, flights, trains, bookings, and users.

## Features

### Dashboard
- Overview statistics of bookings, users, and revenue
- Recent bookings with quick actions
- Visual data representation of key performance indicators

### Booking Management
- **Hotel Bookings:** View, filter, search, and manage all hotel bookings
- **Flight Bookings:** Track and manage flight reservations
- **Train Bookings:** Monitor and handle train ticket bookings
- Status updates (confirm, cancel, mark as completed)
- Detailed booking information

### Content Management
- **Hotels:** Add, edit, and manage hotel listings with details and images
- **Flights:** Configure flight schedules, routes, and pricing
- **Trains:** Set up train schedules, routes, and fare details

### User Management
- View and manage user accounts
- Track user booking history
- Activate/deactivate user accounts
- Search and filter users

### Admin Management
- Create new admin accounts
- Manage existing admin permissions and access

## Access

The admin panel is accessible only to authorized administrators. To access:

1. Navigate to `/admin/` from the root URL
2. Enter the admin credentials:
   - Email: `gojourneyamitk@admin.com`
   - Password: `Akpatra#@1234`

**Note:** For security reasons, please change the default credentials after the first login.

## Technical Details

- The admin panel is built with PHP, MySQL, HTML, CSS, and JavaScript
- Database tables are automatically created if they don't exist
- Responsive design works on desktop and mobile devices
- Secure authentication and session management

## Data Management

The following data entities are managed through the admin panel:

- **Users:** User accounts and profiles
- **Hotels:** Hotel details, rooms, pricing, amenities
- **Flights:** Flight schedules, routes, pricing
- **Trains:** Train schedules, routes, classes, pricing
- **Bookings:** Reservations for hotels, flights, and trains

## Future Enhancements

- Advanced analytics and reporting
- Invoice generation and download
- Email notifications for booking status changes
- Integration with payment gateways for direct payment processing
- Multi-language support

---

&copy; 2024 GoJourney. All rights reserved. 