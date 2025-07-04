USE [master]
GO
CREATE LOGIN [bakery_user] WITH PASSWORD=N'StrongPassword123!', 
DEFAULT_DATABASE=[BakeryOrderSystem],
CHECK_EXPIRATION=OFF, 
CHECK_POLICY=OFF
GO

USE [BakeryOrderSystem]
GO
CREATE USER [bakery_user] FOR LOGIN [bakery_user]
GO
EXEC sp_addrolemember N'db_owner', N'bakery_user'
GO