# CrowEmail API Documentation

A RESTful email service backend built with Laravel. This system acts as a **medium** - external frontend websites provide everything (sender, receiver, subject, HTML template), and this backend handles the email delivery.

## Architecture

```
Frontend Website (React/Vue/etc.)
         |
         |  Sends: to, from, from_name, subject, html
         v
CrowHub Email API (This Backend)
         |
         |  Uses frontend's HTML, sender info, sends email
         v
Email Provider (SMTP/Mailgun/etc.)
         |
         v
Recipient Email
```

## Base URL

```
http://crowhub.test/api
```

## Endpoints

### 1. Send Email

Send an email where the frontend provides everything including their own HTML template.

**Endpoint:** `POST /api/email/send`

**Request Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| to | string | Yes | Receiver email |
| from | string | Yes | Sender email (from frontend website) |
| from_name | string | No | Sender name (from frontend website) |
| subject | string | No | Email subject |
| html | string | No | Full HTML template from frontend |
| body | string | No | Plain text content |
| data | object | No | Data for blade templates |
| cc | array | No | CC recipients |
| bcc | array | No | BCC recipients |
| reply_to | string | No | Reply-to address |

**Example - Frontend sends their own HTML:**

```javascript
// React/Vue frontend sends this
const response = await fetch('http://your-domain.com/api/email/send', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    // Sender - from frontend website
    from: 'noreply@frontend-website.com',
    from_name: 'Frontend Website Name',
    
    // Receiver
    to: 'customer@example.com',
    
    // Subject
    subject: 'Welcome to Our Service!',
    
    // Frontend's own HTML template
    html: `
      <!DOCTYPE html>
      <html>
      <head>
        <style>
          body { font-family: Arial, sans-serif; }
          .container { padding: 20px; }
        </style>
      </head>
      <body>
        <div class="container">
          <h1>Welcome, John!</h1>
          <p>Thank you for joining our service.</p>
          <p>Your email: john@example.com</p>
        </div>
      </body>
      </html>
    `
  })
});

const result = await response.json();
console.log(result);
```

**Success Response (200):**

```json
{
  "success": true,
  "message": "Email sent successfully",
  "data": {
    "to": "customer@example.com",
    "from": "noreply@frontend-website.com",
    "subject": "Welcome to Our Service!",
    "sent_at": "2024-01-15T10:30:00.000000Z"
  }
}
```

---

### 2. Send Batch Emails

Send multiple emails in a single request.

**Endpoint:** `POST /api/email/send-batch`

**Example:**

```javascript
const response = await fetch('http://your-domain.com/api/email/send-batch', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    emails: [
      {
        to: 'user1@example.com',
        from: 'noreply@frontend.com',
        from_name: 'Frontend Website',
        subject: 'Order Shipped',
        html: '<h1>Your order has been shipped!</h1><p>Order #12345</p>'
      },
      {
        to: 'user2@example.com',
        from: 'noreply@frontend.com',
        from_name: 'Frontend Website',
        subject: 'Order Shipped',
        html: '<h1>Your order has been shipped!</h1><p>Order #12346</p>'
      }
    ]
  })
});
```

---

### 3. List Available Templates

Get list of available blade templates (if using blade templates instead of custom HTML).

**Endpoint:** `GET /api/email/templates`

---

### 4. Test Email Configuration

Check if the email service is configured correctly.

**Endpoint:** `GET /api/email/test`

---

### 5. Health Check

Check if the API is running.

**Endpoint:** `GET /api/health`

---

## Using Blade Templates (Alternative)

If frontend doesn't provide HTML, you can use blade templates:

```json
{
  "to": "customer@example.com",
  "from": "noreply@frontend.com",
  "from_name": "Frontend Website",
  "subject": "Welcome",
  "template": "welcome",
  "data": {
    "name": "John Doe",
    "email": "john@example.com"
  }
}
```

Templates are in `resources/views/emails/templates/`.

---

## React Integration Example

### Custom Hook

```javascript
// hooks/useEmail.js
import { useState, useCallback } from 'react';
import axios from 'axios';

const API_URL = 'http://your-domain.com/api';

export const useEmail = () => {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [success, setSuccess] = useState(null);

  const sendEmail = useCallback(async (emailConfig) => {
    setLoading(true);
    setError(null);
    setSuccess(null);

    try {
      const response = await axios.post(`${API_URL}/email/send`, {
        // Sender - from frontend website
        from: emailConfig.from,
        from_name: emailConfig.fromName,
        
        // Receiver
        to: emailConfig.to,
        
        // Subject
        subject: emailConfig.subject,
        
        // Frontend's own HTML template
        html: emailConfig.html,
        
        // Optional
        cc: emailConfig.cc,
        bcc: emailConfig.bcc,
        reply_to: emailConfig.replyTo
      });
      
      setSuccess(response.data);
      return response.data;
    } catch (err) {
      const errorMessage = err.response?.data?.message || 'Failed to send email';
      setError(errorMessage);
      throw err;
    } finally {
      setLoading(false);
    }
  }, []);

  return { sendEmail, loading, error, success };
};
```

### Component Example

```javascript
// components/ContactForm.jsx
import React from 'react';
import { useEmail } from '../hooks/useEmail';

const ContactForm = () => {
  const { sendEmail, loading, error, success } = useEmail();

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    // Get form data
    const formData = new FormData(e.target);
    const name = formData.get('name');
    const email = formData.get('email');
    const message = formData.get('message');
    
    // Frontend's own HTML template
    const htmlTemplate = `
      <!DOCTYPE html>
      <html>
      <body style="font-family: Arial, sans-serif; padding: 20px;">
        <h2>New Contact Form Submission</h2>
        <p><strong>Name:</strong> ${name}</p>
        <p><strong>Email:</strong> ${email}</p>
        <p><strong>Message:</strong></p>
        <p>${message}</p>
      </body>
      </html>
    `;
    
    try {
      await sendEmail({
        // Sender - from frontend website
        from: 'contact@your-website.com',
        from_name: 'Your Website Name',
        
        // Receiver - your email
        to: 'admin@yourdomain.com',
        
        // Subject
        subject: 'New Contact Form Submission',
        
        // Frontend's own HTML
        html: htmlTemplate
      });
      
      alert('Email sent successfully!');
    } catch (err) {
      console.error('Error:', err);
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      <input name="name" placeholder="Your Name" required />
      <input name="email" type="email" placeholder="Your Email" required />
      <textarea name="message" placeholder="Message" required />
      <button type="submit" disabled={loading}>
        {loading ? 'Sending...' : 'Send'}
      </button>
      {error && <p style={{color: 'red'}}>{error}</p>}
      {success && <p style={{color: 'green'}}>{success.message}</p>}
    </form>
  );
};

export default ContactForm;
```

---

## Configuration

### Environment Variables (.env)

```env
# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="CrowEmail"
```

### Supported Mail Drivers

- `smtp` - SMTP (default)
- `log` - Log to file (for testing)
- `sendmail` - Sendmail
- `ses` - Amazon SES
- `postmark` - Postmark

---

## Error Handling

All API responses follow a consistent format:

```json
{
  "success": true,
  "message": "Human readable message",
  "data": { ... },
  "error": "Error details (on failure)"
}
```

### HTTP Status Codes

- `200` - Success
- `422` - Validation Error
- `500` - Server Error
