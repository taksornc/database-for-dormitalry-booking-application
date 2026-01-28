มีๆ อันนี้เรายังไม่ได้ปรับ normalization กับ เพิ่มคําอธิบายนะ ต้องเช็คก่อนว่า ผิดพลาดไปจาก er  dependencydiagram  กับ realationschema ไหม
ถ้าผิดพลาดอย่างไรเดี๋ยวเราปรับตามให้ครับ
11:15
You sent
CREATE TABLE users (
 UserID int(11) NOT NULL AUTO_INCREMENT,
 Username varchar(20) NOT NULL,
 PasswordHash varchar(255) NOT NULL,
 Email varchar(30) NOT NULL,
 Phone varchar(15) NOT NULL,
 CreatedAt timestamp NOT NULL DEFAULT current_timestamp(),
 UpdatedAt timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
 PRIMARY KEY (UserID)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE admin (
 AdminID varchar(10) NOT NULL,
 Username varchar(255) NOT NULL,
 PasswordHash varchar(255) NOT NULL,
 Email varchar(255) NOT NULL,
 Phone varchar(15) DEFAULT NULL,
 Status enum('active','inactive') NOT NULL DEFAULT 'active',
 CreatedAt timestamp NOT NULL DEFAULT current_timestamp(),
 UpdatedAt timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
 PRIMARY KEY (AdminID),
 UNIQUE KEY Username (Username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE adminstaff (
 StaffID int(11) NOT NULL AUTO_INCREMENT,
 FirstName varchar(15) NOT NULL,
 LastName varchar(15) NOT NULL,
 Phone varchar(10) NOT NULL,
 Role varchar(15) NOT NULL,
 PRIMARY KEY (StaffID)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE room (
 RoomID int(11) NOT NULL AUTO_INCREMENT,
 RoomType varchar(15) NOT NULL,
 MonthlyRent decimal(7,2) NOT NULL,
 RoomStatus varchar(15) NOT NULL,
 Floor int(2) NOT NULL,
 Size decimal(5,2) NOT NULL,
 Facilities varchar(50) DEFAULT NULL,
 PRIMARY KEY (RoomID)
) ENGINE=InnoDB AUTO_INCREMENT=510 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE tenant (
 TenantID int(11) NOT NULL AUTO_INCREMENT,
 UserID int(11) NOT NULL,
 FirstName varchar(15) NOT NULL,
 LastName varchar(15) NOT NULL,
 Email varchar(20) NOT NULL,
 Phone varchar(10) NOT NULL,
 IDCardNumber char(13) NOT NULL,
 Address varchar(150) NOT NULL,
 PRIMARY KEY (TenantID),
 KEY fk_user (UserID),
 CONSTRAINT fk_user FOREIGN KEY (UserID) REFERENCES users (UserID) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE booking (
 BookingID int(11) NOT NULL AUTO_INCREMENT,
 TenantID int(11) NOT NULL,
 RoomID int(11) NOT NULL,
 BookingDate date NOT NULL DEFAULT curdate(),
 CheckInDate date NOT NULL,
 CheckOutDate date NOT NULL,
 DepositPaid decimal(7,2) NOT NULL,
 TotalAmount decimal(7,2) NOT NULL,
 PaymentStatus varchar(15) NOT NULL DEFAULT 'Pending',
 AdvancePaid decimal(7,2) NOT NULL,
 PRIMARY KEY (BookingID),
 KEY fk_booking_tenant (TenantID),
 KEY fk_booking_room (RoomID),
 CONSTRAINT fk_booking_room FOREIGN KEY (RoomID) REFERENCES room (RoomID),
 CONSTRAINT fk_booking_tenant FOREIGN KEY (TenantID) REFERENCES tenant (TenantID)
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE utilityusage (
 UtilityID int(11) NOT NULL AUTO_INCREMENT,
 RoomID int(11) NOT NULL,
 BillDate date NOT NULL,
 WaterUsage decimal(7,2) NOT NULL,
 ElectricityUsage decimal(7,2) NOT NULL,
 WaterBill decimal(7,2) NOT NULL,
 ElectricityBill decimal(7,2) NOT NULL,
 TotalUtilityCost decimal(7,2) NOT NULL,
 PRIMARY KEY (UtilityID),
 KEY RoomID (RoomID)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE lease_agreement (
 LeaseID int(11) NOT NULL AUTO_INCREMENT,
 TenantID int(11) NOT NULL,
 BookingID int(11) NOT NULL,
 StartDate date NOT NULL,
 EndDate date NOT NULL,
 RentAmount decimal(7,2) NOT NULL,
 DepositAmount decimal(7,2) NOT NULL,
 Terms text NOT NULL,
 CreatedAt timestamp NOT NULL DEFAULT current_timestamp(),
 UpdatedAt timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
 PRIMARY KEY (LeaseID),
 KEY fk_lease_tenant (TenantID),
 KEY fk_lease_booking (BookingID),
 CONSTRAINT fk_lease_booking FOREIGN KEY (BookingID) REFERENCES booking (BookingID)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE payment (
 PaymentID int(11) NOT NULL AUTO_INCREMENT,
 TenantID int(11) NOT NULL,
 BookingID int(11) NOT NULL,
 UtilityID int(11) DEFAULT NULL,
 PaymentType varchar(15) NOT NULL,
 AmountPaid decimal(7,2) NOT NULL DEFAULT 0.00,
 PaymentDate date NOT NULL,
 PaymentMethod varchar(15) NOT NULL,
 LateFee decimal(7,2) DEFAULT 0.00,
 PaymentStatus varchar(15) NOT NULL DEFAULT 'Pending',
 Receipt varchar(50) DEFAULT NULL,
 PRIMARY KEY (PaymentID),
 KEY TenantID (TenantID),
 KEY BookingID (BookingID),
 KEY UtilityID (UtilityID),
 CONSTRAINT fk_payment_utility FOREIGN KEY (UtilityID) REFERENCES utilityusage (UtilityID)
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE maintenancerequest (
 RequestID int(11) NOT NULL AUTO_INCREMENT,
 TenantID int(11) NOT NULL,
 RoomID int(11) NOT NULL,
 RequestDate date NOT NULL,
 IssueDescription text NOT NULL,
 Status enum('Pending','In Progress','Completed') NOT NULL DEFAULT 'Pending',
 StaffID int(11) DEFAULT NULL,
 PRIMARY KEY (RequestID),
 KEY TenantID (TenantID),
 KEY RoomID (RoomID),
 KEY StaffID (StaffID)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE contactmessages (
 MessageID int(11) NOT NULL AUTO_INCREMENT,
 Name varchar(255) NOT NULL,
 Email varchar(255) NOT NULL,
 Phone varchar(15) NOT NULL,
 Message text NOT NULL,
 MessageDate date NOT NULL,
 Status varchar(50) NOT NULL,
 PRIMARY KEY (MessageID)
) ENGINE=InnoDB AUTO_INCREMENT=8013 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- เพิ่ม Foreign Key Constraint สำหรับตาราง MaintenanceRequest
ALTER TABLE maintenancerequest
ADD CONSTRAINT fk_maintenance_tenant FOREIGN KEY (TenantID) REFERENCES tenant (TenantID),
ADD CONSTRAINT fk_maintenance_room FOREIGN KEY (RoomID) REFERENCES room (RoomID),
ADD CONSTRAINT fk_maintenance_staff FOREIGN KEY (StaffID) REFERENCES adminstaff (StaffID);

-- เพิ่ม Foreign Key Constraint สำหรับตาราง Payment ที่ยังขาด
ALTER TABLE payment
ADD CONSTRAINT fk_payment_tenant FOREIGN KEY (TenantID) REFERENCES tenant (TenantID),
ADD CONSTRAINT fk_payment_booking FOREIGN KEY (BookingID) REFERENCES booking (BookingID);

-- เพิ่ม Foreign Key Constraint สำหรับ Lease_Agreement ที่ยังขาด
ALTER TABLE lease_agreement
ADD CONSTRAINT fk_lease_tenant FOREIGN KEY (TenantID) REFERENCES tenant (TenantID);

-- เพิ่ม Foreign Key Constraint สำหรับ UtilityUsage
ALTER TABLE utilityusage
ADD CONSTRAINT fk_utility_room FOREIGN KEY (RoomID) REFERENCES room (RoomID);