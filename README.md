# INSAT : Social Club

Welcome to the INSAT Social Club repository! INSAT Social Club is a Social Media platform designed to help you connect with friends and family, share your thoughts, ideas, and memories, and stay connected no matter where you are. With real-time chat functionality, you can easily keep in touch with your loved ones.

<p align="center">
  <img src="https://i.imgur.com/K8prkdB.gif" alt="INSAT Social Club Demo">
</p>

## Tech Stack:
![Symfony](https://img.icons8.com/color/48/000000/symfony.png)
![Vue.js](https://img.icons8.com/color/48/000000/vue-js.png)
![JavaScript](https://img.icons8.com/color/48/000000/javascript.png)
![HTML](https://img.icons8.com/color/48/000000/html-5.png)
![CSS](https://img.icons8.com/color/48/000000/css3.png)


## Project Setup:
### Prerequisites:
- **XAMPP**: Ensure you have XAMPP installed on your machine. Download and install it from the official website if you don't have it already: [XAMPP Download](https://www.apachefriends.org/download.html).
- **Apache & MySQL Services**: Make sure both Apache and MySQL services are running within XAMPP.
- **Composer**: Composer is required for dependency management. If not installed, follow the installation guide here: [Composer Download](https://getcomposer.org/download/).
-**Node.js**: Node.js is required for the Vue.js frontend. If not installed, follow the installation guide here: [Node.js Download](https://nodejs.org/en/download/).
- **Symfony CLI**: Symfony CLI is required to run the Symfony server. If not installed, follow the installation guide here: [Symfony CLI Installation](https://symfony.com/download).
- **Yarn**: Yarn is required for dependency management. If not installed, follow the installation guide here: [Yarn Installation](https://classic.yarnpkg.com/en/docs/install/).

### Steps:
1. **Clone the Project**: Clone the project repository using Git (assuming you have Git installed).

2. **Install Dependencies**: Open a terminal or command prompt and navigate to the project directory. Then, execute the following commands in sequence:
bash
   ```
   cd backend
   composer install
   ```
   ```
   cd frontend
   npm install
   ```

4. **Database Setup**:
    - Open your browser and navigate to `http://localhost/phpmyadmin/`.
    - Create a new database named `insat_social_club`.
    - Run Migration to create the database schema. Open a terminal or command prompt and navigate to the `backend` directory. Then, execute the following command: 
      ```
      php bin/console doctrine:migrations:migrate
      ```
5. **Run the Backend Symfony Server**:
    - Navigate to the `backend` directory:
   ```
   cd backend
   ```
    - Start the Symfony server:
   ```
   symfony server:start
   ```

6. **Run the Frontend Vue.js Server**:
    - Navigate to the `frontend` directory:
   ```
   cd frontend
   ```
    - Start the Vue.js server:
   ```
   npm run serve
   ```

### Customize configuration:
See [Configuration Reference](https://cli.vuejs.org/config/).

By following these setup instructions, you'll be ready to explore and run the INSAT Social Club project. Enjoy connecting with your friends and family!

### Video Walkthrough:
For a detailed walkthrough of the project setup and execution, check out the video below:

[![INSAT Social Club Video Walkthrough](https://img.youtube.com/vi/RM31fijAZvc/0.jpg)](https://youtu.be/RM31fijAZvc)

### Contributors:
- Youssef Sghairi
- Mohamed Yassine Tayeb
- Youssef Aridhi
- Mohamed Amine Haouas
- Kacem Mathlouthi
- Ayoub Akremi