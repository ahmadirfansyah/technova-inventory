pipeline {
    agent any

    environment {
        SONAR_PROJECT_KEY = 'technova-inventory'
    }

    stages {
        stage('Clone Repository') {
            steps {
                git branch: 'main', url: 'https://github.com/ahmadirfansyah/technova-inventory.git'
            }
        }

        stage('Install Dependencies') {
            parallel {
                stage('Inventory Service') {
                    steps {
                        dir('inventory-service') {
                            sh 'composer install --no-interaction --prefer-dist'
                        }
                    }
                }
                stage('Order Service') {
                    steps {
                        dir('order-service') {
                            sh 'composer install --no-interaction --prefer-dist'
                        }
                    }
                }
            }
        }

        stage('Unit Test') {
            parallel {
                stage('Inventory Service Tests') {
                    steps {
                        dir('inventory-service') {
                            sh './vendor/bin/phpunit --colors=never'
                        }
                    }
                }
                stage('Order Service Tests') {
                    steps {
                        dir('order-service') {
                            sh './vendor/bin/phpunit --colors=never'
                        }
                    }
                }
            }
            post {
                success { echo 'Semua unit test berhasil!' }
                failure { echo 'Ada unit test yang gagal!' }
            }
        }

        stage('SonarQube Analysis') {
            steps {
                withCredentials([string(credentialsId: 'sonar-token', variable: 'SONAR_TOKEN')]) {
                    sh '''
                        sonar-scanner \
                          -Dsonar.host.url=http://localhost:9000 \
                          -Dsonar.login=$SONAR_TOKEN
                    '''
                }
            }
        }
         
        stage('Build Docker Images') {
            steps {
                sh 'docker compose build'
            }
        }

        stage('Deploy') {
            steps {
                sh 'docker compose up -d'
                echo 'Inventory service: http://localhost:8081'
                echo 'Order service: http://localhost:8082'
                echo 'Grafana: http://localhost:3000'
                echo 'Prometheus: http://localhost:9090'
            }
        }
    }

    post {
        always {
            echo 'Pipeline selesai dijalankan.'
        }
    }
}
