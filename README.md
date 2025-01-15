# Consumer Authentication Service

The Consumer Authentication Service is a microservice designed to handle api consumers authentication and authorization within the Hire-Now platform. It manages api consumers registration (via command), JWT token generation, and validation.

## Features

- API Consumers registration via command
- JWT token generation and validation

## Technologies Used

- PHP 8.3
- Slim Framework (Microframework)
- MySQL
- JSON Web Tokens (JWT)
- Docker (Docker compose)
- Redis

## Getting Started

### Prerequisites

- PHP 8.3
- MySQL
- Docker

### Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/Hire-Now/consumer-auth-service.git
   cd consumer-auth-service
   ```

2. **Install dependencies:**
   ```bash
   composer install
   ```

3. **Set up environment variables:**
   - Rename `.env.example` to `.env`.
   - Update the variables in `.env` with your configuration.

4. **Start the application:**
   ```bash
    docker compose up -d --build
   ```

## Message Routes

The Consumer Authentication Service uses a message-based communication model to handle authentication operations. Below are the message routes and their respective handlers:

### Routes

1. **Authenticate User**  
   - **Channel**: `auth_consumer_channel`  
   - **Message Type**: `authenticate`  
   - **Handler**: `[ConsumerController::class, 'authenticate']`  
   - **Description**: Processes authentication requests by validating user credentials and generating a JWT token.

2. **Validate Token**  
   - **Channel**: `auth_consumer_channel`  
   - **Message Type**: `check-token`  
   - **Handler**: `[ConsumerController::class, 'validateToken']`  
   - **Description**: Validates the JWT token to ensure its authenticity and checks its expiration status.

### Workflow Example

- **Authenticate User**:
  - The client publishes a message to the `auth_consumer_channel` with the type `authenticate`, including the user credentials in the payload.
  - The `ConsumerController::authenticate` handler processes the message and responds with a JWT token.

- **Validate Token**:
  - The client publishes a message to the `auth_consumer_channel` with the type `check-token`, providing the token in the payload.
  - The `ConsumerController::validateToken` handler verifies the token and responds with its validity and expiration information.


*Note: Detailed documentation soon*

## Testing

*Note: Testing will be implemented soon*

## Contribution

We welcome contributions to enhance the Consumer Authentication Service. Please fork the repository and submit a pull request with your changes.

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

## Contact

For any inquiries or support, please contact [nicolas.estevez@plopster.com.co](mailto:nicolas.estevez@plopster.com.co).
