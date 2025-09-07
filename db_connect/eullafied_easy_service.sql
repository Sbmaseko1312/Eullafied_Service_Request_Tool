-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.41 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.11.0.7065
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping data for table eullafied_eazy_service.department: ~8 rows (approximately)
INSERT INTO `department` (`department_id`, `name`) VALUES
	(1, 'Human Resources'),
	(2, 'IT'),
	(3, 'Finance'),
	(4, 'Marketing'),
	(5, 'Operations'),
	(6, 'Logisticss'),
	(8, 'Legal'),
	(10, 'Research and Development');

-- Dumping data for table eullafied_eazy_service.request_type: ~2 rows (approximately)
INSERT INTO `request_type` (`request_type_id`, `name`) VALUES
	(1, 'Hardware'),
	(2, 'Software');

-- Dumping data for table eullafied_eazy_service.role: ~4 rows (approximately)
INSERT INTO `role` (`role_id`, `role_name`) VALUES
	(1, 'Admin'),
	(2, 'Manager'),
	(3, 'IT Staff'),
	(4, 'Employeee');

-- Dumping data for table eullafied_eazy_service.service_request: ~3 rows (approximately)
INSERT INTO `service_request` (`request_id`, `user_id`, `request_type_id`, `description`, `status`, `created_at`, `updated_at`, `staff_id`) VALUES
	(1, 6, 1, 'keyboard not working', 'Pending Assistance', '2025-08-04 15:03:43', '2025-08-04 16:58:12', 10),
	(2, 6, 1, 'fix', 'Solved', '2025-08-04 15:06:38', '2025-08-04 17:00:28', 6),
	(3, 2, 1, 'laptop', 'Pending Assistance', '2025-08-04 18:32:43', '2025-09-07 18:02:06', 2);

-- Dumping data for table eullafied_eazy_service.user: ~9 rows (approximately)
INSERT INTO `user` (`user_id`, `full_name`, `email`, `password_hash`, `department_id`, `role_id`, `created_at`) VALUES
	(1, 'Admin', 'admin@mail.com', 'admin', 2, 1, '2025-09-06 07:39:48'),
	(2, 'Manager One', 'manager1@example.com', 'manager1', 2, 2, '2025-08-04 13:05:40'),
	(3, 'Manager Two', 'manager2@example.com', 'manager2', 3, 2, '2025-08-04 13:05:40'),
	(4, 'Manager Three', 'manager3@example.com', 'manager3', 4, 4, '2025-08-04 13:05:40'),
	(5, 'Employee One', 'emp1@example.com', 'emp1', 2, 3, '2025-08-04 13:05:40'),
	(6, 'Employee Two', 'emp2@example.com', 'emp2', 2, 4, '2025-08-04 13:05:40'),
	(7, 'Employee Three', 'emp3@example.com', 'emp3', 3, 4, '2025-08-04 13:05:40'),
	(9, 'Employee Five', 'emp5@example.com', 'emp5', 5, 4, '2025-08-04 13:05:40'),
	(10, 'Employee Six', 'emp6@example.com', 'emp6', 6, 3, '2025-08-04 13:05:40');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
